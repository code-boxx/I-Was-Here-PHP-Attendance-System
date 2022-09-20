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
        cb.modal("Error", err.message);
        console.log(err);
      }

      // (A4) API CALL
      cb.api({
        mod : "attendance", req : "attendQR",
        data : {
          id : data.i,
          hash : data.h
        },
        passmsg : false,
        onpass : () => cb.modal("OK", "Your attendance has been taken.")
      });
    });
  }
};
window.addEventListener("load", check.init);