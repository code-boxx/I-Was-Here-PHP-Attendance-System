var unfc = {
  // (A) SHOW WRITE NFC PAGE
  hnBtn : null, // html write nfc button
  hnStat : null, // html write nfc button status
  hnNull : null, // html null token button
  show : id => cb.load({
    page : "A/users/nfc", target : "cb-page-2",
    data : { id : id },
    onload : () => {
      unfc.hnBtn = document.getElementById("nfc-btn");
      unfc.hnStat = document.getElementById("nfc-stat");
      unfc.hnNull = document.getElementById("nfc-null");
      if ("NDEFReader" in window) {
        unfc.hnStat.innerHTML = "Create Login Token";
        unfc.hnBtn.disabled = false;
      } else {
        unfc.hnStat.innerHTML = "Web NFC not available";
      }
      cb.page(2);
    }
  }),

  // (B) CREATE NEW NFC LOGIN TAG
  add : id => {
    // (B1) DISABLE "WRITE NFC" BUTTON
    unfc.hnBtn.disabled = true;

    // (B2) REGISTER WITH SERVER + GET JWT
    cb.api({
      mod : "nfcin", act : "add",
      data : { id : id },
      passmsg : false,
      onpass : res => {
        // (B2-1) ENABLE "NULLIFY" BUTTTON
        unfc.hnNull.disabled = false;

        // (B2-2) ON SUCCESSFUL NFC WRITE
        nfc.onwrite = () => {
          nfc.standby();
          cb.modal("Successful", "Login token successfully created.");
          unfc.hnStat.innerHTML = "Done";
        };

        // (B2-3) ON FAILED NFC WRITE
        nfc.onerror = err => {
          nfc.stop();
          console.error(err);
          cb.modal("ERROR", err.message);
          unfc.hnStat.innerHTML = "ERROR!";
          unfc.hnBtn.disabled = false;
        };

        // (B2-4) START NFC WRITE
        nfc.write(res.data);
        unfc.hnStat.innerHTML = "Tap NFC tag to write";
      }
    })
  },

  // (C) NULLIFY NFC TOKEN
  del : id => cb.api({
    mod : "nfcin", act : "del",
    data : { id : id },
    passmsg : "Login token nullified.",
    onpass : res => unfc.hnNull.disabled = true
  }),

  // (D) END WRITE NFC SESSION
  back : () => {
    nfc.stop();
    cb.page(1);
  }
};