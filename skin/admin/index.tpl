{extends file="layout.tpl"}
{block name='head:title'}{#thematic#|ucfirst}{/block}
{block name='body:id'}thematic{/block}

{block name='article:header'}
    {if {employee_access type="append" class_name=$cClass} eq 1}
    <div class="pull-right hidden-ph hidden-xs">
        <p class="text-right">
            {#nbr_pages#|ucfirst}: {$pages|count}<a href="{$smarty.server.SCRIPT_NAME}?controller={$smarty.get.controller}&amp;action=add" title="{#add_thematic#}" class="btn btn-link">
                <span class="fa fa-plus"></span> {#add_thematic#|ucfirst}
            </a>
        </p>
    </div>
    {/if}
    <h1 class="h2"><a href="{$smarty.server.SCRIPT_NAME}?controller={$smarty.get.controller}" title="Afficher la liste des pages">{#thematic#|ucfirst}</a></h1>
{/block}
{block name='article:content'}
    {if {employee_access type="view" class_name=$cClass} eq 1}
    <div class="panels row">
        <section class="panel col-ph-12">
            {if $debug}
                {$debug}
            {/if}
            <header class="panel-header panel-nav">
                <h2 class="panel-heading h5">{#root_thematic#|ucfirst}</h2>
                <ul class="nav nav-tabs" role="tablist">
                    <li role="presentation" class="active"><a href="#list" aria-controls="list" role="tab" data-toggle="tab">{#list#}</a></li>
                    <li role="presentation"><a href="#general" aria-controls="general" role="tab" data-toggle="tab">{#text#}</a></li>
                </ul>
            </header>
            <div class="panel-body panel-body-form">
                <div class="mc-message-container clearfix">
                    <div class="mc-message mc-message-pages">{if isset($message)}{$message}{/if}</div>
                </div>
                <div class="tab-content">
                    <div role="tabpanel" class="tab-pane {if !$smarty.get.plugin}active{/if}" id="list">
                        {*{if isset($scheme)}{$scheme|var_dump}{/if}*}
                        {if $smarty.get.search}{$sortable = false}{else}{$sortable = true}{/if}
                        {include file="section/form/table-form-3.tpl" data=$pages idcolumn='id_tc' activation=true sortable=$sortable controller="thematic" change_offset=true}
                    </div>
                    <div role="tabpanel" class="tab-pane" id="general">
                        {include file="language/brick/dropdown-lang.tpl"}
                        <div class="row">
                            <form id="edit_company_text" action="{$smarty.server.SCRIPT_NAME}?controller={$smarty.get.controller}&amp;action=edit" method="post" class="validate_form edit_form col-ph-12 col-md-10">
                                <div class="tab-content">
                                    {foreach $langs as $id => $iso}
                                        <fieldset role="tabpanel" class="tab-pane{if $iso@first} active{/if}" id="lang-{$id}">
                                            <div class="row">
                                                <div class="col-ph-12 col-sm-8">
                                                    <div class="form-group">
                                                        <label for="content[{$id}][thematic_name]">{#thematic_name#|ucfirst} :</label>
                                                        <input type="text" class="form-control" id="content[{$id}][thematic_name]" name="content[{$id}][thematic_name]" value="{$contentData.{$id}.name}" size="50" placeholder="{#thematic_name_ph#|ucfirst}" />
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label for="content[{$id}][thematic_content]">{#content#|ucfirst} :</label>
                                                <textarea name="content[{$id}][thematic_content]" id="content[{$id}][thematic_content]" class="form-control mceEditor">{call name=cleantextarea field=$contentData.{$id}.content}</textarea>
                                            </div>
                                            <div class="form-group">
                                                <button class="btn collapsed btn-collapse" role="button" data-toggle="collapse" data-parent="#accordion" href="#metas-{$id}" aria-expanded="true" aria-controls="metas-{$id}">
                                                    <span class="fa"></span> {#display_metas#|ucfirst}
                                                </button>
                                            </div>

                                            <div id="metas-{$id}" class="collapse" role="tabpanel" aria-labelledby="heading{$id}">
                                                <div class="row">
                                                    <div class="col-ph-12 col-sm-8">
                                                        <div class="form-group">
                                                            <label for="content[{$id}][seo_title]">{#title#|ucfirst} :</label>
                                                            <textarea class="form-control" id="content[{$id}][seo_title]" name="content[{$id}][seo_title]" cols="70" rows="3">{$contentData[{$id}].seo_title}</textarea>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-ph-12 col-sm-8">
                                                        <div class="form-group">
                                                            <label for="content[{$id}][seo_desc]">Description :</label>
                                                            <textarea class="form-control" id="content[{$id}][seo_desc]" name="content[{$id}][seo_desc]" cols="70" rows="3">{$contentData[{$id}].seo_desc}</textarea>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </fieldset>
                                    {/foreach}
                                </div>
                                <button class="btn btn-main-theme pull-right" type="submit" name="type" value="root">{#save#|ucfirst}</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
    {include file="modal/delete.tpl" data_type='thematic' title={#modal_delete_title#|ucfirst} info_text=true delete_message={#delete_pages_message#}}
    {include file="modal/error.tpl"}
    {else}
    {include file="section/brick/viewperms.tpl"}
{/if}
{/block}

{block name="foot" append}
    {capture name="scriptForm"}{strip}
        /{baseadmin}/min/?f=
        libjs/vendor/jquery-ui-1.12.min.js,
        {baseadmin}/template/js/table-form.min.js,
        plugins/thematic/js/admin.min.js
    {/strip}{/capture}
    {script src=$smarty.capture.scriptForm type="javascript"}
    {include file="section/footer/editor.tpl"}
    <script type="text/javascript">
        $(function(){
            var controller = "{$smarty.server.SCRIPT_NAME}?controller={$smarty.get.controller}";
            var offset = "{if isset($offset)}{$offset}{else}null{/if}";
            if (typeof tableForm == "undefined")
            {
                console.log("tableForm is not defined");
            }else{
                tableForm.run(controller,offset);
            }
        });
    </script>
{/block}