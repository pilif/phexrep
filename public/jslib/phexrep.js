var Exrep = {};
Exrep.Configuration = {
    builtin_fields: { type: true, id: true, ts: true, message: true, uri: true, error_info: true}
};


(function($) {
    Exrep.app = $.sammy((function(){
        var renderReportList = function(context, ps){
            ps = ps || 10;
            $.ajax({
                url: "./api.php/exceptions?pagesize="+encodeURI(ps),
                dataType: "json",
                success: function(reports){
                    context.partial('templates/report_list.template', {reports: reports});
                    $('#showpage').hide();
                }
            })
        }

        return function(){
            this.element_selector = '#main';
            this.use(Sammy.Template);

            this.get('#/', function(context){
                var pagesize = context.params.ps || 10;
                renderReportList(context, pagesize);
            });

            this.get('#/report/:id', function(context){
                var pagesize = context.params.ps || 10;
                if ($('#exceptions > h1').length === 0)
                    renderReportList(context, pagesize);
                $.ajax({
                    url: "./api.php/exceptions/"+ encodeURI(this.params['id']),
                    dataType: "json",
                    success: function(report){
                        context.partial('templates/report_detail.template', {report: report}, function(r){
                            $('#showpage').html(r).show();
                        });
                    }
                })
            });
        };
    })());
    $(function() {
        Exrep.app.run('#/');
    });
})(jQuery);