var qrSession = {
  timer : null,
  isOpen : false,
  refreshing : false,
  api : async (action, data) => {
    let form = new FormData();
    for (let [key, value] of Object.entries(data || {})) { form.append(key, value); }
    let response = await fetch(`${qrApiBase}attend/${action}/`, {
      method : "POST", credentials : "include", body : form
    });
    let text = await response.text();
    let result;
    try { result = JSON.parse(text); }
    catch (err) { throw new Error("The server returned an invalid response."); }
    if (!response.ok || !result.status) { throw new Error(result.message || "The request failed."); }
    return result.data;
  },
  state : async () => {
    try {
      let state = await qrSession.api("sessionState", { id : qrClassId });
      qrSession.show(!!state && state.session_status === "open");
      document.getElementById("session-status").textContent =
        state && state.session_status === "open"
          ? `Attendance is open since ${state.opened_at}.`
          : "Attendance is closed.";
      document.getElementById("session-message").textContent = "";
    } catch (err) {
      document.getElementById("session-message").textContent = err.message;
    }
  },
  show : open => {
    qrSession.isOpen = open;
    document.getElementById("session-open").hidden = open;
    document.getElementById("session-close").hidden = !open;
    if (open) {
      qrSession.refresh();
      if (qrSession.timer) { clearInterval(qrSession.timer); }
      qrSession.timer = setInterval(qrSession.refresh, 20000);
    } else {
      if (qrSession.timer) { clearInterval(qrSession.timer); qrSession.timer = null; }
      document.getElementById("qrcode").replaceChildren();
      document.getElementById("qr-expiry").textContent = "";
    }
  },
  refresh : async () => {
    if (qrSession.refreshing || !qrSession.isOpen) { return; }
    qrSession.refreshing = true;
    try {
      let result = await qrSession.api("qrToken", { id : qrClassId });
      if (!qrSession.isOpen) { return; }
      let target = document.getElementById("qrcode");
      target.replaceChildren();
      new QRCode(target, {
        text : JSON.stringify({ t : result.token }),
        width : 256, height : 256,
        colorDark : "#000000",
        colorLight : "#ffffff",
        correctLevel : QRCode.CorrectLevel.H
      });
      document.getElementById("qr-expiry").textContent =
        `Code refreshes automatically; expires ${new Date(result.expires * 1000).toLocaleTimeString()}.`;
      document.getElementById("session-message").textContent = "";
    } catch (err) {
      document.getElementById("qrcode").replaceChildren();
      document.getElementById("qr-expiry").textContent = "";
      document.getElementById("session-message").textContent = err.message;
      try {
        let state = await qrSession.api("sessionState", { id : qrClassId });
        if (!state || state.session_status !== "open") {
          qrSession.show(false);
          document.getElementById("session-status").textContent = "Attendance is closed.";
        }
      } catch (stateError) {
        document.getElementById("session-message").textContent = stateError.message;
      }
    } finally {
      qrSession.refreshing = false;
    }
  },
  open : async () => {
    try {
      await qrSession.api("openSession", { id : qrClassId });
      await qrSession.state();
    } catch (err) {
      document.getElementById("session-message").textContent = err.message;
    }
  },
  close : async () => {
    try {
      await qrSession.api("closeSession", { id : qrClassId });
      qrSession.show(false);
      document.getElementById("session-status").textContent = "Attendance is closed.";
      document.getElementById("session-message").textContent = "";
    } catch (err) {
      document.getElementById("session-message").textContent = err.message;
    }
  }
};

window.addEventListener("load", qrSession.state);
