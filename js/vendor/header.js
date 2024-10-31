// JS para ocultar y mostrar menu responsive
jQuery(document).ready(function ($){
  function myFunction3() {

    var x = document.getElementById("myDIV3");
    if (x.style.display === "block") {
      x.style.display = "none";
    } else {
      x.style.display = "block";
    }
  }

  jQuery(document).ready(function() {
    jQuery(document).foundation();
    $('#myDIV3').css('display','none');
  });




//nuevo onclick


  $( ".menuresponsivecolor" ).click(function() {
    myFunction3();
  });

// JS para ocultar y mostrar menu offcanvas

  function myFunction4() {

    var y = document.getElementById("offCanvasStatic");
    if (y.style.display === "contents") {
      y.style.display = "none";
    } else {
      y.style.display = "contents";
    }
  }

});















