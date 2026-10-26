var myCorrections = {
  load : () => cb.api({
    mod : "attend", act : "getCorrections",
    passmsg : false, loading : false,
    onpass : result => myCorrections.render(result.data || [])
  }),
  render : requests => {
    let target = document.getElementById("my-correction-list");
    target.replaceChildren();
    if (!requests.length) {
      target.textContent = "You have not submitted any correction requests.";
      return;
    }
    for (let request of requests) {
      let card = document.createElement("section");
      card.className = "border bg-white p-3 mb-3";
      let title = document.createElement("h4");
      title.textContent = `[${request.course_code}] ${request.course_name} — ${request.class_date}`;
      let status = document.createElement("p");
      status.textContent = `Requested: ${request.requested_status == 1 ? "Present" : "Absent"}; status: ${request.request_status}.`;
      let reason = document.createElement("p");
      reason.textContent = `Your reason: ${request.request_reason}`;
      card.append(title, status, reason);
      if (request.decided_at) {
        let decision = document.createElement("p");
        decision.textContent = `Decision by ${request.decider_name || "staff"} on ${request.decided_at}: ${request.decision_note || ""}`;
        card.append(decision);
      }
      target.append(card);
    }
  }
};

window.addEventListener("load", myCorrections.load);
