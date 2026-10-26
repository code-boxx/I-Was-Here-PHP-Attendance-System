var corrections = {
  load : () => cb.api({
    mod : "attend", act : "getCorrections",
    passmsg : false, loading : false,
    onpass : result => corrections.render(result.data || [])
  }),
  render : items => {
    let list = document.getElementById("correction-list");
    list.replaceChildren();
    if (!items.length) {
      list.textContent = "No correction requests found.";
      return;
    }
    for (let item of items) {
      let card = document.createElement("section");
      card.className = "border bg-white p-3 mb-3";
      let heading = document.createElement("h4");
      heading.textContent = `[${item.course_code}] ${item.course_name} — ${item.user_name}`;
      let detail = document.createElement("p");
      detail.textContent = `${item.class_date}: requested ${item.requested_status == 1 ? "Present" : "Absent"}; current status: ${item.request_status}.`;
      let reason = document.createElement("p");
      reason.textContent = `Student reason: ${item.request_reason}`;
      card.append(heading, detail, reason);
      if (item.request_status === "pending") {
        let approve = document.createElement("button");
        approve.type = "button";
        approve.className = "btn btn-success me-2";
        approve.textContent = "Approve";
        approve.addEventListener("click", () => corrections.decide(item.request_id, "approved"));
        let reject = document.createElement("button");
        reject.type = "button";
        reject.className = "btn btn-outline-danger";
        reject.textContent = "Reject";
        reject.addEventListener("click", () => corrections.decide(item.request_id, "rejected"));
        card.append(approve, reject);
      } else {
        let decision = document.createElement("p");
        decision.textContent = `Decision: ${item.request_status} by ${item.decider_name || "staff"} on ${item.decided_at}. ${item.decision_note || ""}`;
        card.append(decision);
      }
      list.append(card);
    }
  },
  decide : (requestId, decision) => cb.modal(
    decision === "approved" ? "Approve correction" : "Reject correction",
    `<label class="form-label" for="decision-note">Decision reason (required)</label>
     <textarea id="decision-note" class="form-control" maxlength="500" required></textarea>`,
    () => {
      let note = document.getElementById("decision-note").value.trim();
      if (!note) {
        cb.modal("Reason required", "Please provide a reason for the decision.");
        return;
      }
      cb.api({
        mod : "attend", act : "decideCorrection",
        data : { requestId : requestId, decision : decision, note : note },
        passmsg : "Correction request updated",
        onpass : corrections.load
      });
    }
  )
};

window.addEventListener("load", corrections.load);
