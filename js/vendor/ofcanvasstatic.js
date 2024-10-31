

jQuery(document).ready(function ($){
    /* off canvas estatico */
    $( document ).ready(function() {

        $('#offCanvasStatic').foundation('open');


        var offcanvasestatico = 0;

        offcanvasstaticfunciont(offcanvasestatico);

    });

    function offcanvasstaticfunciont(offcanvasestatico){

        if (offcanvasestatico == 0){
            $('.section-size-privado').css({
                'width': '77%'
            });
            offcanvasestatico = 1;
        } else {
            $('.section-size-privado').css({
                'width': '100%'
            });
            offcanvasestatico = 0;
        }
    }






    $( document ).ready(function() {

        resizemobil();

        $(window).resize(function() {

            resizemobil();

        });

    });



    function resizemobil(){
        if($(window).width() < 1023){

            $("#offCanvasStatic").css("display", "none");

            $('.section-size-privado').css({
                'width': '100%'
            });
            $('.pushonlymobil').css({
                'margin-top': '250px'
            });

//estatico
            $( "#offCanvasStatic" ).removeClass( "canvas-estatico off-canvas position-left offcanvasfijo is-transition-push" ).addClass( "canvas-estatico off-canvas position-top offcanvasfijo is-transition-push" );

            // $('.off-canvas-content').css({
            //   'transform': 'none'
            //  });





//workflow


        } else {

            $("#offCanvasStatic").css("display", "block");

            $( "#offCanvasStatic" ).removeClass( "canvas-estatico off-canvas position-top offcanvasfijo is-transition-push" ).addClass( "canvas-estatico off-canvas position-left offcanvasfijo is-transition-push" );


            $('.section-size-privado').css({
                'width': '77%'
            });

            $('.pushonlymobil').css({
                'margin-top': '0px'
            });

            // $('.off-canvas-content.is-open-left.has-transition-push').css({
            //   'transform': 'translateX(250px)'
            //  });

        }
    }
});



