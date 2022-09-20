window.addEventListener("load", () => {
  selector.attach({
    field : document.getElementById("attend-course"),
    mod : "autocomplete", req : "course",
    pick : (d, v) => {
      document.getElementById("attend-course").value = "";
      document.getElementById("attend-id").value = v;
      document.getElementById("report-attend").submit();
    }
  });
});