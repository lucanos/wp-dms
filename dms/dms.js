(function($){

  var controls = {

    init : function(){
      var that = this;
      $( '.dms-delete-row' )
        .live( 'click' , function(){
          that.removeRow( $( this ) );
        });
      $( '.dms-add-row' )
        .live( 'click' , function( e ){
          e.preventDefault();
          that.addRow( $( this ) );
        });
    } ,

    removeRow: function( btn ){
      $( btn ).closest( 'tr' )
        .remove();
    } ,

    addRow : function( btn ){
      var tr = $( '#dms-map' ).find( 'tr' ).get(1) ,
          clone = $( tr ).clone() ,
          select = $(clone).find('a.chzn-single').first();
      $( clone ).find( 'input' )
        .each(function(){
          $( this ).val( '' );
        });
      select
        .addClass( 'chzn-default chzn-single-with-drop' )
        .find( 'span' )
          .text( $( '.chzn-done' ).first().attr( 'data-placeholder' ) );
      $( clone )
        .removeAttr( 'id' );
      $( '#dms-add-new-tr' )
        .before( clone );
    }

  };

  var chosen = {

    init : function(){
      $( 'select.dms' )
        .chosen();
    }

  };

  $(document).ready(function(){
    controls.init();
    chosen.init();
  });

})(jQuery);
