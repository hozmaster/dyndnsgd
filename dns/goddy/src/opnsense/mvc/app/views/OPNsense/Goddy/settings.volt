{#

This file is Copyright © 2021 Olli-Pekka Wallin
All rights reserved.

Redistribution and use in source and binary forms, with or without modification,
are permitted provided that the following conditions are met:

1.  Redistributions of source code must retain the above copyright notice,
    this list of conditions and the following disclaimer.

2.  Redistributions in binary form must reproduce the above copyright notice,
    this list of conditions and the following disclaimer in the documentation
    and/or other materials provided with the distribution.

THIS SOFTWARE IS PROVIDED “AS IS” AND ANY EXPRESS OR IMPLIED WARRANTIES,
INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY
AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
AUTHOR BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY,
OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
POSSIBILITY OF SUCH DAMAGE.

#}

<script>

    $( document ).ready(function() {

        var data_get_map = {'frm_settings':"/api/goddy/settings/get"};
        mapDataToFormUI(data_get_map).done(function(data){
            formatTokenizersUI();
            $('.selectpicker').selectpicker('refresh');
        });

        $("#saveAct").click(function(){
            saveFormToEndpoint(url="/api/goddy/settings/set", formid='frm_settings',callback_ok=function() {
            $('.selectpicker').selectpicker('refresh');
                $("#saveAct_progress").addClass("fa fa-spinner fa-pulse");
                     ajaxCall(url="/api/goddy/service/reconfigure", sendData={}, callback=function(data,status) {
                         setTimeout(function () {
                             $("#saveAct_progress").removeClass("fa fa-spinner fa-pulse");
                         }, 500);
                });
            });
        });

        $("#fetchAct").click(function(){
            $("#responseMsg").html("Processing ... ");
            $("#responseMsg").removeClass("hidden");
            $("#fetchAct_progress").addClass("fa fa-spinner fa-pulse");
            ajaxCall(url="/api/goddy/settings/fetch", sendData={}, callback=function(data,status) {
               $("#responseMsg").html(data['message']);
               $("#fetchAct_progress").removeClass("fa fa-spinner fa-pulse");
               setTimeout(function () {
                   $("#responseMsg").addClass("hidden");
                }, 5000);
            });
    });
});

</script>

<div class="alert alert-info hidden" role="alert" id="responseMsg"> </div>

<ul class="nav nav-tabs" data-tabs="tabs" id="settings_tabs">
    <li class="active"><a data-toggle="tab" href="#settings">{{ lang._('Settings') }}</a></li>
</ul>

<div class="tab-content content-box tab-content">
    <div id="general" class="tab-pane fade in active">
        <div class="content-box" style="padding-bottom: 1.5em;">
            {{ partial("layout_partials/base_form",['fields':formViewSettings,'id':'frm_settings'])}}
            <div class="col-md-12">
                <hr />
                <button class="btn btn-primary" id="saveAct" type="button"><b>{{ lang._('Save') }}</b> <i id="saveAct_progress"></i></button>
                <button class="btn btn-primary" id="fetchAct" type="button"><b>{{ lang._('Fetch') }}</b> <i id="fetchAct_progress"></i></button>
            </div>
        </div>
    </div>
</div>
