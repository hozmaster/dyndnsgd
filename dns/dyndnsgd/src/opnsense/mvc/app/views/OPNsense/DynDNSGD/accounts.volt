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
            search:'/api/dyndnsgd/accounts/search',
            get:'/api/dyndnsgd/accounts/get/',
            set:'/api/dyndnsgd/accounts/update/',
            add:'/api/dyndnsgd/accounts/add/',
            del:'/api/dyndnsgd/accounts/del/',
            toggle:'/api/dyndnsgd/accounts/toggle/',
            fetch:'/api/dyndnsgd/accounts/fetch/',
        };

        var gridopt = {
            ajax: true,
            selection: true,
            multiSelect: true,
            rowCount:[10,25,50,100,500,1000],
            url: '/api/dyndnsgd/accounts/search',
            formatters: {
                "commands": function (column, row) {
                    return "<button type=\"button\" class=\"btn btn-xs btn-default command-edit\" data-row-id=\"" + row.uuid + "\"><span class=\"fa fa-pencil\"></span></button> " +
                        "<button type=\"button\" class=\"btn btn-xs btn-default command-delete\" data-row-id=\"" + row.uuid + "\"><span class=\"fa fa-trash-o\"></span></button>" +
                        "<button type=\"button\" class=\"btn btn-xs btn-default command-fetch\" data-row-id=\"" + row.uuid + "\"><span class=\"fa fa-address-book-o\"></span></button>"
                        ;
                },
                "rowtoggle": function (column, row) {
                    if (parseInt(row[column.id], 2) == 1) {
                        return "<span style=\"cursor: pointer;\" class=\"fa fa-check-square-o command-toggle\" data-value=\"1\" data-row-id=\"" + row.uuid + "\"></span>";
                    } else {
                        return "<span style=\"cursor: pointer;\" class=\"fa fa-square-o command-toggle\" data-value=\"0\" data-row-id=\"" + row.uuid + "\"></span>";
                    }
                },
                "account-type": function (column, row) {
                    if (row.staging == "0" || row.staging == undefined) {
                        return "{{ lang._('No') }}";
                    } else {
                        return "{{ lang._('Yes') }}";
                    }
                }
            } // formatter
        }; // gridopt

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
        var grid_accounts = $("#grid-accounts").bootgrid(gridopt).on("loaded.rs.jquery.bootgrid", function (e)
        {
            // scale footer on resize
            $(this).find("tfoot td:first-child").attr('colspan',$(this).find("th").length - 1);
            $(this).find('tr[data-row-id]').each(function(){
                if ($(this).find('[class*="command-toggle"]').first().data("value") == "0") {
                    $(this).addClass("text-muted");
                }
            });

            // edit dialog id to use
            var editDlg = $(this).attr('data-editDialog');
            var gridId = $(this).attr('id');

            // link Add new to child button with data-action = add
            $(this).find("*[data-action=add]").click(function(){
                if ( gridParams['get'] != undefined && gridParams['add'] != undefined) {
                    var urlMap = {};
                    urlMap['frm_' + editDlg] = gridParams['get'];
                    mapDataToFormUI(urlMap).done(function(){
                        // update selectors
                        formatTokenizersUI();
                        $('.selectpicker').selectpicker('refresh');
                        // clear validation errors (if any)
                        clearFormValidation('frm_' + editDlg);
                    });

                    // show dialog for edit
                    $('#'+editDlg).modal({backdrop: 'static', keyboard: false});
                    //
                    $("#btn_"+editDlg+"_save").unbind('click').click(function(){
                        saveFormToEndpoint(url=gridParams['add'],
                            formid='frm_' + editDlg, callback_ok=function(){
                                $("#"+editDlg).modal('hide');
                                $("#"+gridId).bootgrid("reload");
                            }, true);
                    });
                }  else {
                    console.log("[grid] action add missing")
                }

            });

            // link delete selected items action
            $(this).find("*[data-action=delete]").click(function(){
                if ( gridParams['del'] != undefined) {
                    stdDialogConfirm('{{ lang._('Confirm removal') }}',
                        '{{ lang._('Do you want to remove the selected item?') }}',
                        '{{ lang._('Yes') }}', '{{ lang._('Cancel') }}', function () {
                        var rows =$("#"+gridId).bootgrid('getSelectedRows');
                        if (rows != undefined){
                            var deferreds = [];
                            $.each(rows, function(key,uuid){
                                deferreds.push(ajaxCall(url=gridParams['del'] + uuid, sendData={},null));
                            });
                            // refresh after load
                            $.when.apply(null, deferreds).done(function(){
                                std_bootgrid_reload(gridId);
                            });
                        }
                    });
                } else {
                    console.log("[grid] action del missing")
                }
            });

         });

        grid_accounts.on("loaded.rs.jquery.bootgrid", function() {

            // edit dialog id to use
            var editDlg = $(this).attr('data-editDialog');
            var gridId = $(this).attr('id');

            // edit item
            grid_accounts.find(".command-edit").on("click", function(e)
            {
                if (editDlg != undefined && gridParams['get'] != undefined) {
                    var uuid = $(this).data("row-id");
                    var urlMap = {};
                    urlMap['frm_' + editDlg] = gridParams['get'] + uuid;
                    mapDataToFormUI(urlMap).done(function () {
                        // update selectors
                        formatTokenizersUI();
                        $('.selectpicker').selectpicker('refresh');
                        // clear validation errors (if any)
                        clearFormValidation('frm_' + editDlg);
                    });

                    // show dialog for pipe edit
                    $('#'+editDlg).modal({backdrop: 'static', keyboard: false});
                    // define save action
                    $("#btn_"+editDlg+"_save").unbind('click').click(function(){
                        if (gridParams['set'] != undefined) {
                            saveFormToEndpoint(url=gridParams['set']+uuid,
                                formid='frm_' + editDlg, callback_ok=function(){
                                    $("#"+editDlg).modal('hide');
                                    std_bootgrid_reload(gridId);
                                }, true);
                        } else {
                            console.log("[grid] action set missing")
                        }
                    });
                } else {
                    console.log("[grid] action get or data-editDialog missing")
                }
            });

            // delete item
            grid_accounts.find(".command-delete").on("click", function(e)
            {
                if (gridParams['del'] != undefined) {
                    var uuid=$(this).data("row-id");
                    stdDialogConfirm('{{ lang._('Confirm removal') }}',
                        '{{ lang._('Do you want to remove the selected item?') }}',
                        '{{ lang._('Yes') }}', '{{ lang._('Cancel') }}', function () {
                        ajaxCall(url=gridParams['del'] + uuid,
                            sendData={},callback=function(data,status){
                                // reload grid after delete
                                $("#"+gridId).bootgrid("reload");
                            });
                    });
                } else {
                    console.log("[grid] action del missing")
                }
            });

            // fetch all domains from service.
            grid_accounts.find(".command-fetch").on("click", function(e)
            {
                if (gridParams['fetch'] != undefined) {
                    var uuid=$(this).data("row-id");
                    stdDialogConfirm('{{ lang._('Confirmation Required') }}',
                        '{{ lang._('Fetch all domains which are related to account from service ?') }}',
                        '{{ lang._('Yes') }}', '{{ lang._('Cancel') }}', function() {
                        ajaxCall(url=gridParams['fetch'] + uuid,sendData={},callback=function(data,status){
                            // reload grid afterwards
                            $("#"+gridId).bootgrid("reload");
                        });
                    });
                } else {
                    console.log("[grid] action fetch domains missing")
                }
            });


            // toggle item, enable or disable domain
            grid_accounts.find(".command-toggle").on("click", function(e)
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

    });     // ready

</script>

<ul class="nav nav-tabs" data-tabs="tabs" id="maintabs">
    <li class="active"><a data-toggle="tab" href="#accounts">{{ lang._('Accounts') }}</a></li>
</ul>
    <div class="tab-content content-box tab-content">
    <div id="accounts" class="tab-pane fade in active">
    <table id="grid-accounts" class="table table-condensed table-hover table-striped table-responsive" data-editDialog="dialogEditServiceAccount">
        <thead>
            <tr>
                <th data-column-id="enabled" data-type="string" data-width="4em" data-formatter="rowtoggle">{{ lang._('Enabled') }}</th>
                <th data-column-id="name" data-width="8em" data-type="string">{{ lang._('Name') }}</th>
                <th data-column-id="service_provider" data-width="6em" data-type="string" data-visible="true">{{ lang._('Service') }}</th>
                <th data-column-id="description" data-width="8em" data-type="string">{{ lang._('Description') }}</th>
                <th data-column-id="staging" data-width="3em" data-sortable="false" data-formatter="account-type" >{{ lang._('Staging') }}</th>
                <th data-column-id="commands" data-width="5em" data-formatter="commands" data-sortable="false">{{ lang._('Commands') }}</th>
                <th data-column-id="uuid" data-type="string" data-width="6em" data-identifier="true" data-visible="false">{{ lang._('ID') }}</th>
            </tr>
        </thead>
        <tbody>
        </tbody>
        <tfoot>
            <tr>
                <td></td>
                <td>
                    <button data-action="add" type="button" data-toggle="tooltip" data-placement="right" title={{ lang._('Add') }} class="btn btn-xs btn-default"><span class="fa fa-plus"></span></button>
                </td>
            </tr>
        </tfoot>
    </table>
    </div>
</div>


{{ partial("layout_partials/base_dialog",['fields':formDialogEditServiceAccount,'id':'dialogEditServiceAccount','label':lang._('Add account')])}}
