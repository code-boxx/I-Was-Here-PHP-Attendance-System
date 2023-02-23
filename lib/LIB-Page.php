<?php
class Page extends Core {
  // (A) DRAW HTML PAGINATION SQUARES
  //  $action : URL link or Javascript function
  //  $mode : "J"avascript function or "A"nchor links
  //  $adj : number of adjcent page squares
  function draw ($action, $mode="J", $adj=1) : void {
  if ($this->Core->page != null && $this->Core->page["total"]!=0) {
    echo "<ul class='pagination p-2 m-0'>";

    // (A1) ENOUGH PAGES TO HIDE - DRAW WITH ... SQUARES
    if ($this->Core->page["total"]>5 + ($adj*2)) {
      // (A1-1) CURRENT PAGE IS CLOSE TO BEGINNING - HIDE LATER PAGES
      if ($this->Core->page["now"] < 2 + ($adj*2)) {
        for ($i=1; $i<3 + ($adj*2); $i++) {
          $this->cell($i, $action, $mode, $i==$this->Core->page["now"]);
        }
        $this->cell("...");
        for ($i=$this->Core->page["total"]-1; $i<=$this->Core->page["total"]; $i++) {
          $this->cell($i, $action, $mode, $i==$this->Core->page["now"]);
        }
      }

      // (A1-2) CURRENT PAGE SOMEWHERE IN THE MIDDLE
      else if ($this->Core->page["total"] - ($adj*2) > $this->Core->page["now"] && $this->Core->page["now"] > ($adj*2)) {
        for ($i=1; $i<3; $i++) {
          $this->cell($i, $action, $mode, $i==$this->Core->page["now"]);
        }
        $this->cell("...");
        for ($i=$this->Core->page["now"]-$adj; $i<=$this->Core->page["now"]+$adj; $i++) {
          $this->cell($i, $action, $mode, $i==$this->Core->page["now"]);
        }
        $this->cell("...");
        for ($i=$this->Core->page["total"]-1; $i<=$this->Core->page["total"]; $i++) {
          $this->cell($i, $action, $mode, $i==$this->Core->page["now"]);
        }
      }

      // (A1-3) CURRENT PAGE SOMEWHERE IN THE MIDDLE - HIDE EARLY PAGES
      else {
        for ($i=1; $i<3; $i++) {
          $this->cell($i, $action, $mode, $i==$this->Core->page["now"]);
        }
        $this->cell("...");
        for ($i=$this->Core->page["total"] - (2+($adj * 2)); $i<=$this->Core->page["total"]; $i++) {
          $this->cell($i, $action, $mode, $i==$this->Core->page["now"]);
        }
      }
    }

    // (A2) NOT ENOUGH PAGES - JUST DRAW ALL
    else {
      for ($i=1; $i<=$this->Core->page["total"]; $i++) {
        $this->cell($i, $action, $mode, $i==$this->Core->page["now"]);
      }
    }
    echo "</ul>";
  }}

  // (B) SUPPORT FUNCTION, DRAW AN HTML PAGINATION CELL
  //  $pg : page number (or text)
  //  $action : URL link or Javascript function
  //  $mode : "J"avascript function or "A"nchor links
  //  $current : is current page?
  function cell ($pg, $action=null, $mode="J", $current=false) : void {
    // (B1) OPENING <LI> TAG
    echo "<li class='page-item". ($current ? " active" : "") ."'>";

    // (B2) INNER <A> OR <SPAN>
    if ($mode=="A") {
      $tag = "a";
      $act = $action!==null ? " href='$action?pg=$pg'" : "";
    } else {
      $tag = "span";
      $act = $action!==null ? " onclick='$action($pg)'" : "";
    }
    echo "<$tag class='page-link'$act>$pg</$tag>";

    // (B3) CLOSING </LI> TAG
    echo "</li>";
  }
}