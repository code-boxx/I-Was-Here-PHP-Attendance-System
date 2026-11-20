var classes = {
  // (A) SHOW ALL CLASSES
  pg : 1, // current page
  range : "", // search range
  date : "", // search date
  list : () => cb.load({
    page : "U/class/list",
    target : "class-list",
    data : {
      page : classes.pg,
      range : classes.range,
      date : classes.date
    }
  }),

  // (B) GO TO PAGE
  //  pg : int, page number
  goToPage : pg => { if (pg!=classes.pg) {
    classes.pg = pg;
    classes.list();
  }},

  // (C) SEARCH FORM TOGGLE
  stog : () => 
  document.getElementById("search-date").disabled = 
  document.getElementById("search-range").value=="" ? true : false ,

  // (D) SEARCH FOR CLASS
  search : () => {
    classes.range = document.getElementById("search-range").value;
    classes.date = document.getElementById("search-date").value;
    classes.pg = 1;
    classes.list();
    return false;
  },

  queueKey : () => {
    let uid = document.getElementById("offline-queue-list")?.dataset.userId || "unknown";
    return `iwh-offline-checkins-${uid}`;
  },

  queuedLocally : () => {
    try { return JSON.parse(localStorage.getItem(classes.queueKey()) || "[]"); }
    catch (err) {
      document.getElementById("offline-queue-message").textContent =
        "Local offline storage is unavailable in this browser.";
      return [];
    }
  },

  saveLocalQueue : queue => {
    try {
      localStorage.setItem(classes.queueKey(), JSON.stringify(queue));
      return true;
    } catch (err) {
      document.getElementById("offline-queue-message").textContent =
        "Could not save this check-in on the device. Check available browser storage.";
      return false;
    }
  },

  formatTimestamp : date => {
    let pad = value => String(value).padStart(2, "0");
    return `${date.getFullYear()}-${pad(date.getMonth()+1)}-${pad(date.getDate())} ${pad(date.getHours())}:${pad(date.getMinutes())}:${pad(date.getSeconds())}`;
  },

  queueOffline : (classId, button) => {
    if (!confirm("Queue an unverified check-in for teacher review? Your device's time can be changed and will not be treated as proof of attendance.")) {
      return;
    }
    let queue = classes.queuedLocally();
    let eventId = window.crypto?.randomUUID
      ? window.crypto.randomUUID()
      : `offline-${Date.now()}-${Math.random().toString(36).slice(2)}`;
    queue.push({
      eventId : eventId,
      classId : Number(classId),
      classDate : button.dataset.classDate,
      recordedAt : classes.formatTimestamp(new Date()),
      localStatus : "pending"
    });
    if (!classes.saveLocalQueue(queue)) { return; }
    button.disabled = true;
    button.textContent = "Queued on this device";
    classes.drawOfflineQueue();
    classes.syncOfflineQueue();
  },

  drawOfflineQueue : () => {
    let target = document.getElementById("offline-queue-list");
    if (!target) { return; }
    target.replaceChildren();
    let queue = classes.queuedLocally();
    for (let item of queue) {
      let row = document.createElement("div");
      row.className = "border p-2 d-flex justify-content-between align-items-center gap-2";
      let detail = document.createElement("span");
      detail.className = "small";
      detail.textContent = `Class ${item.classId}: saved ${item.recordedAt} on this device; waiting to upload.`;
      let remove = document.createElement("button");
      remove.type = "button";
      remove.className = "btn btn-sm btn-outline-secondary";
      remove.textContent = "Discard";
      remove.setAttribute("aria-label", `Discard local check-in for class ${item.classId}`);
      remove.addEventListener("click", () => classes.discardLocal(item.eventId));
      row.append(detail, remove);
      target.append(row);
    }
  },

  discardLocal : eventId => {
    let remaining = classes.queuedLocally().filter(item => item.eventId!==eventId);
    if (!classes.saveLocalQueue(remaining)) { return; }
    classes.drawOfflineQueue();
    classes.syncOfflineQueue();
  },

  syncOfflineQueue : () => {
    let message = document.getElementById("offline-queue-message");
    if (!message) { return; }
    let queue = classes.queuedLocally();
    if (!queue.length) {
      classes.refreshOfflineQueue();
      return;
    }
    if (!navigator.onLine) {
      message.textContent = `${queue.length} check-in(s) are saved on this device and will upload when online.`;
      return;
    }
    message.textContent = "Uploading saved check-ins…";
    let uploadNext = () => {
      let current = classes.queuedLocally();
      if (!current.length) {
        message.textContent = "Saved check-ins uploaded. They are awaiting teacher review.";
        classes.refreshOfflineQueue();
        return;
      }
      let item = current[0];
      cb.api({
        mod : "attend", act : "queueOfflineAttendance",
        data : {
          classId : item.classId,
          eventId : item.eventId,
          status : "1",
          timestamp : item.recordedAt
        },
        loading : false, passmsg : false, nofail : true, noerr : true,
        onpass : () => {
          classes.saveLocalQueue(current.slice(1));
          uploadNext();
        },
        onfail : error => {
          message.textContent = `Could not upload check-in for class ${item.classId}: ${error}`;
        },
        onerr : () => {
          message.textContent = "Connection failed. Saved check-ins remain on this device for retry.";
        }
      });
    };
    uploadNext();
  },

  refreshOfflineQueue : () => {
    let target = document.getElementById("offline-queue-list");
    let message = document.getElementById("offline-queue-message");
    if (!target || !message || !navigator.onLine) {
      classes.drawOfflineQueue();
      return;
    }
    cb.api({
      mod : "attend", act : "getStudentOfflineQueue",
      passmsg : false, loading : false, noerr : true,
      onpass : result => {
        target.replaceChildren();
        let rows = result.data || [];
        for (let item of rows) {
          let row = document.createElement("div");
          row.className = "border p-2 small";
          row.textContent = `[${item.course_code}] ${item.course_name} — ${item.class_date}: ${item.status}; device time ${item.recorded_at}${item.review_note ? `; note: ${item.review_note}` : ""}`;
          target.append(row);
        }
        let pendingLocal = classes.queuedLocally().length;
        message.textContent = pendingLocal
          ? `${pendingLocal} check-in(s) remain on this device; uploaded records await teacher review.`
          : "Uploaded check-ins appear here until reviewed by a teacher.";
      },
      onfail : error => { message.textContent = `Unable to load uploaded check-ins: ${error}`; },
      onerr : () => { message.textContent = "Connection failed while loading uploaded check-ins."; }
    });
  }
};
window.addEventListener("online", classes.syncOfflineQueue);
window.addEventListener("load", () => {
  classes.list();
  classes.drawOfflineQueue();
  classes.syncOfflineQueue();
});