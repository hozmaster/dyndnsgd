{#
 # Copyright (c) 2021 Wallin Olli-Pekka
 # All rights reserved.
 #
 # Redistribution and use in source and binary forms, with or without modification,
 # are permitted provided that the following conditions are met:
 #
 # 1. Redistributions of source code must retain the above copyright notice,
 #    this list of conditions and the following disclaimer.
 #
 # 2. Redistributions in binary form must reproduce the above copyright notice,
 #    this list of conditions and the following disclaimer in the documentation
 #    and/or other materials provided with the distribution.
 #
 # THIS SOFTWARE IS PROVIDED “AS IS” AND ANY EXPRESS OR IMPLIED WARRANTIES,
 # INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY
 # AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
 # AUTHOR BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY,
 # OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
 # SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
 # INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
 # CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 # ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 # POSSIBILITY OF SUCH DAMAGE.
#}

<script>

    $( document ).ready(function() {

        var gridParams = {
            search:'/api/dyndnsgd/domains/search',
            get:'/api/dyndnsgd/domains/get/',
            set:'/api/dyndnsgd/domains/update/',
            del:'/api/dyndnsgd/domains/del/',
            toggle:'/api/dyndnsgd/domains/toggle/',
        };

        var gridopt = {
            ajax: true,
            selection: true,
            multiSelect: true,
            rowCount:[10,25,50,100,500,1000],
            url: '/api/dyndnsgd/domains/search',
            formatters: {
                "commands": function (column, row) {
                    return "<button type=\"button\" class=\"btn btn-xs btn-default command-edit\" data-row-id=\"" + row.uuid + "\"><span class=\"fa fa-pencil\"></span></button> " +
                        "<button type=\"button\" class=\"btn btn-xs btn-default command-delete\" data-row-id=\"" + row.uuid + "\"><span class=\"fa fa-trash-o\"></span></button>" ;

                },
                "rowtoggle": function (column, row) {
                    if (parseInt(row[column.id], 2) == 1) {
                        return "<span style=\"cursor: pointer;\" class=\"fa fa-check-square-o command-toggle\" data-value=\"1\" data-row-id=\"" + row.uuid + "\"></span>";
                    } else {
                        return "<span style=\"cursor: pointer;\" class=\"fa fa-square-o command-toggle\" data-value=\"0\" data-row-id=\"" + row.uuid + "\"></span>";
                    }
                }
            }
        };

        /**
         * reload bootgrid, return to current selected page
         */
        function std_bootgrid_reload(gridId) {
            var currentpage = $("#"+gridId).bootgrid("getCurrentPage");
            $("#"+gridId).bootgrid("reload");
            // absolutely not perfect, bootgrid.reload doesn't seem to support when().done()
            setTimeout(function(){
                $('#'+gridId+'-footer  a[data-page="'+currentpage+'"]').click();
            }, 400);
        }

        /**
         * copy actions for selected items from opnsense_bootgrid_plugin.js
         */
        var grid_domains = $("#grid-domains").bootgrid(gridopt).on("loaded.rs.jquery.bootgrid", function (e)
        {
            // scale footer on resize
            $(this).find("tfoot td:first-child").attr('colspan',$(this).find("th").length - 1);
            $(this).find('tr[data-row-id]').each(function(){
                if ($(this).find('[class*="command-toggle"]').first().data("value") == "0") {
                    $(this).addClass("text-muted");
                }
            });

            // add/edit dialog id to use
            var domainDlg = $(this).attr('data-editDialog');
            var gridId = $(this).attr('id');

            // link Add new to child button with data-action = add
            $(this).find("*[data-action=add]").click(function(){
                if ( gridParams['get'] != undefined && gridParams['add'] != undefined) {
                    var urlMap = {};
                    urlMap['frm_' + domainDlg] = gridParams['get'];
                    mapDataToFormUI(urlMap).done(function(){
                        // update selectors
                        formatTokenizersUI();
                        $('.selectpicker').selectpicker('refresh');
                        // clear validation errors (if any)
                        clearFormValidation('frm_' + domainDlg);
                    });

                    // show dialog for edit
                    $('#'+domainDlg).modal({backdrop: 'static', keyboard: false});
                    $("#btn_"+domainDlg+"_save").unbind('click').click(function(){
                        saveFormToEndpoint(url=gridParams['add'],
                            formid='frm_' + domainDlg, callback_ok=function(){
                                $("#"+domainDlg).modal('hide');
                                $("#"+gridId).bootgrid("reload");
                            }, true);
                    });
                }  else {
                    console.log("[grid] action add missing")
                }
            });
        });

        /**
         * copy actions for items from opnsense_bootgrid_plugin.js
         */
        grid_domains.on("loaded.rs.jquery.bootgrid", function(){

            // edit dialog id to use
            var domainDlg = $(this).attr('data-editDialog');
            var gridId = $(this).attr('id');

            // edit item
            grid_domains.find(".command-edit").on("click", function(e)
            {
                if (domainDlg != undefined && gridParams['get'] != undefined) {
                    var uuid = $(this).data("row-id");
                    var urlMap = {};
                    urlMap['frm_' + domainDlg] = gridParams['get'] + uuid;
                    mapDataToFormUI(urlMap).done(function () {
                        // update selectors
                        formatTokenizersUI();
                        $('.selectpicker').selectpicker('refresh');
                        // clear validation errors (if any)
                        clearFormValidation('frm_' + domainDlg);
                    });

                    // show dialog for pipe edit
                    $('#'+domainDlg).modal({backdrop: 'static', keyboard: false});
                    // define save action
                    $("#btn_"+domainDlg+"_save").unbind('click').click(function(){
                        if (gridParams['set'] != undefined) {
                            saveFormToEndpoint(url=gridParams['set']+uuid,
                                formid='frm_' + domainDlg, callback_ok=function(){
                                    $("#"+domainDlg).modal('hide');
                                    std_bootgrid_reload(gridId);
                                }, true);
                        } else {
                            console.log("[grid] action set missing")
                        }
                    });
                } else {
                    console.log("[grid] action get or data-domainDlg missing")
                }
            });


            // delete item
            grid_domains.find(".command-delete").on("click", function(e)
            {
                if (gridParams['del'] != undefined) {
                    var uuid=$(this).data("row-id");
                    stdDialogConfirm('{{ lang._('Confirm removal') }}',
                        '{{ lang._('Do you want to remove the selected item?') }}',
                        '{{ lang._('Yes') }}', '{{ lang._('Cancel') }}', function () {
                        ajaxCall(url=gridParams['del'] + uuid,
                            sendData={},callback=function(data,status) {
                                // reload grid after delete
                                $("#"+gridId).bootgrid("reload");
                            });
                    });
                } else {
                    console.log("[grid] action del missing")
                }
            });

            // toggle item, enable or disable domain
            grid_domains.find(".command-toggle").on("click", function(e)
            {
                 if (gridParams['toggle'] != undefined) {
                    var uuid=$(this).data("row-id");
                    $(this).addClass("fa-spinner fa-pulse");
                    ajaxCall(url=gridParams['toggle'] + uuid,
                        sendData={},callback=function(data,status){
                            // reload grid after toggle
                            std_bootgrid_reload(gridId);
                        });
                } else {
                    console.log("[grid] action toggle missing")
                }
            });

        });

    });

</script>


<ul class="nav nav-tabs" data-tabs="tabs" id="domaintabs">
    <li class="active"><a data-toggle="tab" href="#domains">{{ lang._('Domains') }}</a></li>
</ul>

<div class="tab-content content-box tab-content">
    <div id="domains" class="tab-pane fade in active">
    <table id="grid-domains" class="table table-condensed table-hover table-striped table-responsive" data-editDialog="dialogEditDomain">
        <thead>
            <tr>
                <th data-column-id="enabled" data-width="4em" data-type="string" data-formatter="rowtoggle">{{ lang._('Enabled') }}</th>
                <th data-column-id="domain" data-width="8em" data-type="string">{{ lang._('Domain') }}</th>
                <th data-column-id="account" data-width="7em" data-sortable="yes" data-visible="true">{{ lang._('Account') }}</th>
                <th data-column-id="description" data-width="7em" data-sortable="yes">{{ lang._('Description') }}</th>
                <th data-column-id="interface" data-width="4em" data-sortable="yes">{{ lang._('Interface') }}</th>
                <th data-column-id="commands" data-width="5em" data-formatter="commands" data-sortable="false">{{ lang._('Commands') }}</th>
                <th data-column-id="uuid" data-width="8em" data-type="string" data-identifier="true" data-visible="false">{{ lang._('ID') }}</th>
            </tr>
        </thead>
        <tbody>
        </tbody>
        <tfoot>
            <tr>
                <td></td>
            </tr>
        </tfoot>
    </table>
    </div>
</div>

{{ partial("layout_partials/base_dialog",['fields':formDialogEditDomain,'id':'dialogEditDomain','label':lang._('Edit domain')])}}
