(function($) {
   var app = $.sammy(function() {
      this.element_selector = '#main';
      this.use(Sammy.Template);

      this.get('#/', function(context){
          $.ajax({
              url: "./api.php/exceptions",
              dataType: "json",
              success: function(reports){
                  context.partial('templates/report_list.template', {reports: reports});
              }
          })
      });

      this.get('#/:id', function(context){
          $.ajax({
              url: "./api.php/exceptions/"+ encodeURI(this.params['id']),
              dataType: "json",
              success: function(report){
                  context.partial('templates/report_detail.template', {report: report});
              }
          })
       });

      $(function() {
        app.run('#/');
      });
  });

})(jQuery);