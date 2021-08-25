{include file="language/brick/dropdown-lang.tpl"}
<div class="row">
    <form id="add_tc" action="{$smarty.server.SCRIPT_NAME}?controller={$smarty.get.controller}&amp;action=add" method="post" class="validate_form add_form col-ph-12 col-lg-8 collapse in">
        <div class="tab-content">
            <div class="row">
                <div class="col-ph-12 col-md-2">
                    <div class="form-group">
                        <label for="parent_id">{#id#|ucfirst} {#pages#}&nbsp;</label>
                        <input type="text" name="parent_id" id="parent_id" class="form-control mygroup" placeholder="{#ph_id#}" value="{$smarty.get.parent_id}" />
                    </div>
                </div>
                <div class="col-ph-12 col-md-6">
                    <div class="form-group">
                        <label for="parent">{#parent_page#|ucfirst}&nbsp;</label>
                        <div id="parent" class="btn-group btn-block selectpicker" data-clear="true" data-live="true">
                            <a href="#" class="clear"><span class="fa fa-times"></span><span class="sr-only">Annuler la sélection</span></a>
                            <button data-id="parent" type="button" class="btn btn-block btn-default dropdown-toggle">
                                <span class="placeholder">{#ph_pages#|ucfirst}</span>
                                <span class="caret"></span>
                            </button>
                            <div class="dropdown-menu">
                                <div class="live-filtering" data-clear="true" data-autocomplete="true" data-keys="true">
                                    <label class="sr-only" for="input-pages">Rechercher dans la liste</label>
                                    <div class="search-box">
                                        <div class="input-group">
                                            <span class="input-group-addon" id="search-pages">
                                                <span class="fa fa-search"></span>
                                                <a href="#" class="fa fa-times hide filter-clear"><span class="sr-only">Effacer filtre</span></a>
                                            </span>
                                            <input type="text" placeholder="Rechercher dans la liste" id="input-pages" class="form-control live-search" aria-describedby="search-pages" tabindex="1" />
                                        </div>
                                    </div>
                                    <div id="filter-pages" class="list-to-filter">
                                        <ul class="list-unstyled">
                                            {foreach $pagesSelect as $items}
                                                <li class="filter-item items" data-filter="{$items.name_tc}" data-value="{$items.id_tc}" data-id="{$items.id_tc}">
                                                    {$items.name_tc}&nbsp;{if $items.id_parent != '0'}<small>({$items.parent_tc})</small>{/if}
                                                </li>
                                            {/foreach}
                                        </ul>
                                        <div class="no-search-results">
                                            <div class="alert alert-warning" role="alert"><i class="fa fa-warning margin-right-sm"></i>Aucune entrée pour <strong>'<span></span>'</strong> n'a été trouvée.</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-ph-12 col-sm-4">
                    <div class="form-group">
                        <label for="menu_tc">{#menu#}</label>
                        <input id="menu_tc" data-toggle="toggle" type="checkbox" name="menu_tc" data-on="{#visible#}" data-off="{#hidden_f#}" data-onstyle="success" data-offstyle="danger" checked>
                    </div>
                </div>
            </div>
            {foreach $langs as $id => $iso}
                <fieldset role="tabpanel" class="tab-pane{if $iso@first} active{/if}" id="lang-{$id}">
                    <div class="row">
                        <div class="col-ph-12 col-sm-8">
                            <div class="form-group">
                                <label for="content[{$id}][name_tc]">{#title#|ucfirst} :</label>
                                <input type="text" class="form-control" id="content[{$id}][name_tc]" name="content[{$id}][name_tc]" value="{$page.content[{$id}].name_tc}" size="50" />
                            </div>
                        </div>
                        <div class="col-ph-12 col-sm-4">
                            <div class="form-group">
                                <label for="content[{$id}][published_tc]">{#status#|ucfirst}</label>
                                <input id="content[{$id}][published_tc]" data-toggle="toggle" type="checkbox" name="content[{$id}][published_tc]" data-on="Publiée" data-off="Brouillon" data-onstyle="success" data-offstyle="danger"{if $page.content[{$id}].published_tc} checked{/if}>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-ph-12 col-sm-8">
                            <div class="form-group">
                                <label for="content[{$id}][title_tc]">{#short_title#|ucfirst} :</label>
                                <input type="text" class="form-control" id="content[{$id}][title_tc]" name="content[{$id}][title_tc]" value="{$page.content[{$id}].title_tc}" size="50" />
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="content[{$id}][resume_tc]">{#resume#|ucfirst} :</label>
                        <textarea name="content[{$id}][resume_tc]" id="content[{$id}][resume_tc]" class="form-control">{$page.content[{$id}].resume_tc}</textarea>
                    </div>
                    <div class="form-group">
                        <label for="content[{$id}][content_tc]">{#content#|ucfirst} :</label>
                        <textarea name="content[{$id}][content_tc]" id="content[{$id}][content_tc]" class="form-control mceEditor">{call name=cleantextarea field=$page.content[{$id}].content_tc}</textarea>
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
                                    <label for="content[{$id}][seo_title_tc]">{#title#|ucfirst} :</label>
                                    <textarea class="form-control" id="content[{$id}][seo_title_tc]" name="content[{$id}][seo_title_tc]" cols="70" rows="3">{$page.content[{$id}].seo_title_tc}</textarea>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-ph-12 col-sm-8">
                                <div class="form-group">
                                    <label for="content[{$id}][seo_desc_tc]">Description :</label>
                                    <textarea class="form-control" id="content[{$id}][seo_desc_tc]" name="content[{$id}][seo_desc_tc]" cols="70" rows="3">{$page.content[{$id}].seo_desc_tc}</textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </fieldset>
            {/foreach}
        </div>
        <div id="submit" class="col-ph-12 col-md-6">
            <button class="btn btn-main-theme pull-right" type="submit" name="action" value="add">{#save#|ucfirst}</button>
        </div>
    </form>
</div>