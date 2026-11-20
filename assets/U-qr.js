var check = {
  // (A) INIT
  scanner : null, // qr scanner
  storageKey : "iwh-offline-attendance",
  queue : [],
  status : null,
  cameraStatus : null,
  cameraErrorShown : false,
  init : () => {
    check.status = document.getElementById("offline-checkin-status");
    check.cameraStatus = document.getElementById("camera-status");
    check.loadQueue();
    check.renderQueue();
    check.sync();
    window.addEventListener("online", check.sync);

    // (A1) START QR SCANNER
    check.scanner = new Html5QrcodeScanner("reader", {
      fps: 5, qrbox: { width: 250, height: 250 }, rememberLastUsedCamera: true
    });
    check.scanner.render((data, res) => {
      if (check.cameraStatus) { check.cameraStatus.textContent = "QR code detected."; }
      // (A2) STOP ON SCAN
      let buttons = document.querySelectorAll("#reader button");
      buttons[1].click();

      // (A3) GET DATA
      try { data = JSON.parse(data); }
      catch (err) {
        cb.modal("Invalid QR code", "This QR code is not a valid attendance token.");
        return;
      }
      if (!data || typeof data.t !== "string" || data.t.length===0) {
        cb.modal("Invalid QR code", "This QR code does not contain an attendance token.");
        return;
      }

      // (A4) API CALL
      const eventId = `qr-${Date.now()}-${crypto.randomUUID ? crypto.randomUUID() : Math.random().toString(36).slice(2)}`;
      const recordedAt = check.mysqlDate();
      cb.api({
        mod : "attend", act : "attendQR",
        data : { token : data.t },
        passmsg : false,
        nofail : true,
        onpass : () => cb.modal("Attendance recorded", "Your attendance has been taken."),
        onfail : message => {
          if (!navigator.onLine || /network|ajax|failed|fetch/i.test(message)) {
            check.queueEvent(data.t, eventId, recordedAt);
            return;
          }
          cb.modal("Attendance not recorded", message);
        },
        onerr : () => check.queueEvent(data.t, eventId, recordedAt)
      });
    }, error => {
      if (!check.cameraStatus) { return; }
      if (!check.cameraErrorShown && /permission|camera|notallowed|secure/i.test(String(error))) {
        check.cameraErrorShown = true;
        check.cameraStatus.textContent =
          "Camera access was not available. Check browser permissions and HTTPS, then try again.";
      }
    });
  }

  ,mysqlDate : () => {
    const now = new Date();
    const pad = value => String(value).padStart(2, "0");
    return `${now.getFullYear()}-${pad(now.getMonth()+1)}-${pad(now.getDate())} `
      + `${pad(now.getHours())}:${pad(now.getMinutes())}:${pad(now.getSeconds())}`;
  }

  ,loadQueue : () => {
    try {
      const stored = JSON.parse(localStorage.getItem(check.storageKey) || "[]");
      check.queue = Array.isArray(stored) ? stored.filter(item =>
        item && typeof item.eventId==="string" && typeof item.classId==="number" &&
        typeof item.recordedAt==="string" &&
        ["pending", "syncing", "synchronized", "failed", "expired"].includes(item.status)
      ) : [];
    } catch (error) {
      check.queue = [];
      console.error("Could not load offline attendance queue.", error);
    }
  }

  ,saveQueue : () => {
    try {
      localStorage.setItem(check.storageKey, JSON.stringify(check.queue));
    } catch (error) {
      console.error("Could not save offline attendance queue.", error);
    }
  }

  ,renderQueue : () => {
    if (!check.status) { return; }
    check.status.replaceChildren();
    if (!check.queue.length) {
      check.status.textContent = "No offline check-ins waiting to sync.";
      return;
    }
    const pending = check.queue.filter(item => item.status==="pending" || item.status==="syncing").length;
    const failed = check.queue.filter(item => item.status==="failed").length;
    const expired = check.queue.filter(item => item.status==="expired").length;
    check.status.textContent = `${pending} pending, ${failed} failed, ${expired} expired. `
      + "Offline requests remain unverified until a teacher reviews them.";
  }

  ,queueEvent : (token, eventId, recordedAt) => {
    const payload = token.split(".", 1)[0];
    let claims;
    try {
      claims = JSON.parse(atob(payload.replace(/-/g, "+").replace(/_/g, "/")
        .padEnd(Math.ceil(payload.length / 4) * 4, "=")));
    } catch (error) {
      cb.modal("Attendance unavailable", "This QR code could not be queued safely.");
      return;
    }
    if (!Number.isInteger(claims.c) || claims.c < 1) {
      cb.modal("Attendance unavailable", "This QR code does not identify a class.");
      return;
    }
    const existing = check.queue.find(item => item.eventId===eventId);
    if (!existing) {
      check.queue.push({ eventId, classId : claims.c, recordedAt, status : "pending", attempts : 0 });
      check.saveQueue();
    }
    check.renderQueue();
    cb.modal("Attendance queued", navigator.onLine
      ? "The server could not be reached. We will retry automatically."
      : "You are offline. It will sync when a connection is available.");
  }

  ,sync : () => {
    if (!navigator.onLine || !check.queue.length) {
      check.renderQueue();
      return;
    }
    const next = check.queue.find(item => item.status==="pending" || item.status==="failed");
    if (!next) {
      check.renderQueue();
      return;
    }
    next.status = "syncing";
    next.attempts = (next.attempts || 0) + 1;
    check.saveQueue();
    check.renderQueue();
    cb.api({
      mod : "attend", act : "queueOfflineAttendance",
      data : {
        classId : next.classId, eventId : next.eventId, status : "1",
        timestamp : next.recordedAt
      },
      passmsg : false, loading : false, noerr : true, nofail : true,
      onpass : result => {
        const state = result?.data?.status;
        if (state==="pending" || state==="synced") {
          next.status = "synchronized";
          check.saveQueue();
          check.renderQueue();
          cb.toast(true, "Attendance synchronized", "Your request is awaiting teacher review.");
          check.sync();
          return;
        }
        next.status = "failed";
        check.saveQueue();
        check.renderQueue();
      },
      onfail : message => {
        if (/expired|invalid|cancelled|non-instruction|within 30 minutes|48 hours/i.test(message)) {
          next.status = "expired";
        } else {
          next.status = "failed";
        }
        check.saveQueue();
        check.renderQueue();
      },
      onerr : () => {
        next.status = "failed";
        check.saveQueue();
        check.renderQueue();
      }
    });
  }
};
window.addEventListener("load", check.init);