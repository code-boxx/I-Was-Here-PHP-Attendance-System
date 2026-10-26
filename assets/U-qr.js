var check = {
  // (A) INIT
  scanner : null, // qr scanner
  init : () => {
    // (A1) START QR SCANNER
    check.scanner = new Html5QrcodeScanner("reader", { fps: 10, qrbox: 250 });
    check.scanner.render((data, res) => {
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
      cb.api({
        mod : "attend", act : "attendQR",
        data : { token : data.t },
        passmsg : false,
        onpass : () => cb.modal("Attendance recorded", "Your attendance has been taken.")
      });
    });
  }
};
window.addEventListener("load", check.init);