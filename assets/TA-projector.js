var projector = {
  timer : null,
  refresh : () => {
    cb.api({
      mod : "attend", act : "getProjectorStats",
      data : { classId : projectorClassId },
      passmsg : false, loading : false, noerr : true,
      onpass : result => {
        let data = result.data;
        document.getElementById("projector-present").textContent = data.present;
        document.getElementById("projector-late").textContent = data.late;
        document.getElementById("projector-absent").textContent = data.absent;
        document.getElementById("projector-excused").textContent = data.excused ?? 0;
        document.getElementById("projector-unrecorded").textContent = data.unrecorded;
        document.getElementById("projector-progress").textContent =
          `${data.present} of ${data.total} enrolled students marked present`;
        let session = document.getElementById("projector-session");
        session.textContent = `Attendance ${data.session_status}`;
        if (data.class_status==="cancelled") { session.textContent = "Class cancelled"; }
        else if (data.holiday_name) { session.textContent = `Non-instruction day: ${data.holiday_name}`; }
        session.className = `badge fs-5 ${data.session_status==="open" ? "bg-success" : "bg-secondary"}`;
        document.getElementById("projector-updated").textContent =
          `Updated ${new Date().toLocaleTimeString()}; refreshes every 10 seconds.`;
        document.getElementById("projector-error").textContent = "";
      },
      onfail : message => {
        document.getElementById("projector-error").textContent = message;
      },
      onerr : () => {
        document.getElementById("projector-error").textContent =
          "Unable to update attendance. Check the connection and try again.";
      }
    });
  },
  fullscreen : () => {
    if (document.fullscreenElement) {
      document.exitFullscreen();
      return;
    }
    document.documentElement.requestFullscreen().catch(() => {
      document.getElementById("projector-error").textContent =
        "Full-screen mode is unavailable in this browser.";
    });
  }
};

window.addEventListener("load", () => {
  projector.refresh();
  projector.timer = setInterval(projector.refresh, 10000);
  document.getElementById("projector-fullscreen").addEventListener("click", projector.fullscreen);
});
window.addEventListener("beforeunload", () => {
  if (projector.timer) { clearInterval(projector.timer); }
});
