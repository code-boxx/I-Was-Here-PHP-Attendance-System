var reports = {
  type : type => {
    document.getElementById("report-attend").action =
      document.getElementById("report-attend").dataset.reportBase + type;
    document.getElementById("attend-format").value = "csv";
  },
  format : format => {
    document.getElementById("report-attend").action =
      document.getElementById("report-attend").dataset.reportBase + "attend";
    document.getElementById("attend-format").value = format;
  },
  submit : () => {
    let code = document.getElementById("attend-code");
    let from = document.getElementById("attend-from").value;
    let to = document.getElementById("attend-to").value;
    if (!code.value) {
      document.getElementById("report-selection").textContent =
        "Select a course from the suggestions before generating a report.";
      return false;
    }
    if (from && to && from>to) {
      document.getElementById("report-selection").textContent =
        "The start date must not be after the end date.";
      return false;
    }
    return true;
  }
};

window.addEventListener("load", () => {
  let course = document.getElementById("attend-course");
  autocomplete.attach({
    target : course,
    mod : "autocomplete", act : "course",
    onpick : res => {
      document.getElementById("attend-code").value = res.v;
      course.value = `${res.n} (${res.v})`;
      document.getElementById("report-selection").textContent =
        "Selected course: " + course.value;
    }
  });
  course.addEventListener("input", () => {
    document.getElementById("attend-code").value = "";
    document.getElementById("report-selection").textContent =
      "Choose a course from the suggestions to enable report generation.";
  });
});