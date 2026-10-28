var studentAttendance = {
  request : classId => {
    cb.modal(
      "Request attendance correction",
      `<label class="form-label" for="correction-status">Requested status</label>
       <select id="correction-status" class="form-select mb-3">
         <option value="1">Present</option>
         <option value="0">Absent</option>
         <option value="2">Excused</option>
       </select>
       <label class="form-label" for="correction-reason">Reason (required)</label>
       <textarea id="correction-reason" class="form-control" maxlength="500" required></textarea>`,
      () => {
        let reason = document.getElementById("correction-reason").value.trim();
        if (!reason) {
          cb.modal("Reason required", "Please provide a reason for this request.");
          return;
        }
        cb.api({
          mod : "attend", act : "requestCorrection",
          data : {
            classId : classId,
            requestedStatus : document.getElementById("correction-status").value,
            reason : reason
          },
          passmsg : "Correction request submitted",
          onpass : () => classes.list()
        });
      }
    );
  }
};
