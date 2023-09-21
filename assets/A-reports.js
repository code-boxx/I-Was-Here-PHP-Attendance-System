window.addEventListener("load", () => {
  autocomplete.attach({
    target : document.getElementById("attend-course"),
    mod : "autocomplete", act : "course",
    onpick : res => {
      document.getElementById("attend-course").value = "";
      document.getElementById("attend-code").value = res.v;
      document.getElementById("report-attend").submit();
    }
  });
});