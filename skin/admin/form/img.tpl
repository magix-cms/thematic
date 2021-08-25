<div class="row">
    <form id="delete_img_thematic" action="{$smarty.server.SCRIPT_NAME}?controller={$smarty.get.controller}&amp;action=delete" method="post" class="validate_form delete_form_img col-ph-12">
        <div class="form-group">
            <input type="hidden" id="del_img" name="del_img" value="{$page.id_tc}">
            <button class="btn btn-danger" type="submit" name="action" value="img">{#remove#|ucfirst}</button>
        </div>
    </form>
    {foreach $langs as $id => $iso}
        {if $iso@first}{$default = $id}{break}{/if}
    {/foreach}
    <form id="edit_img_thematic" action="{$smarty.server.SCRIPT_NAME}?controller={$smarty.get.controller}&amp;action=edit&amp;edit={$page.id_tc}" method="post" class="validate_form edit_form_img col-ph-12 col-md-6 col-lg-5">
        <h3>Image, description de l'image et SEO</h3>
        <div class="form-group">
            <label for="name_img_{$id}">{#name_img#|ucfirst} :</label>
            <input type="text" class="form-control" placeholder="{#ph_name_img#}" id="name_img_{$id}" name="name_img" value="{if isset($page.img_tc)}{$page.img_tc}{else}{$page.content[$default].url_tc}{/if}" />
        </div>
        <div id="drop-zone" class="img-drop{if !isset($page.imgSrc) || empty($page.imgSrc)} no-img{/if}">
            <div id="drop-buttons" class="form-group">
                <label id="clickHere" class="btn btn-default">
                    ou cliquez ici.. <span class="fa fa-upload"></span>
                    <input type="hidden" name="MAX_FILE_SIZE" value="4048576" />
                    <input type="file" id="img" name="img" />
                    <input type="hidden" id="id_tc" name="id" value="{$page.id_tc}">
                </label>
            </div>
            <div class="preview-img">
                <img id="preview"
                     src="{if isset($page.imgSrc) && !empty($page.imgSrc)}/upload/thematic/{$page.id_tc}/{$page.imgSrc['original'].img}{else}#{/if}"
                     alt="Déposez votre images ici..."
                     class="{if isset($page.imgSrc) && !empty($page.imgSrc)}preview{else}no-img{/if} img-responsive" />
            </div>
        </div>
        {include file="language/brick/dropdown-lang.tpl" onclass="true"}
        <div class="tab-content">
            {foreach $langs as $id => $iso}
                <div role="tabpanel" class="tab-pane{if $iso@first} active{/if} lang-{$id}">
                    <fieldset>
                        <legend>Texte</legend>
                        <div class="row">
                            <div class="col-ph-12 col-md-6">
                                <div class="form-group">
                                    <label for="alt_img_{$id}">{#alt_img#|ucfirst} :</label>
                                    <input type="text" class="form-control" id="alt_img_{$id}" name="content[{$id}][alt_img]" placeholder="{#ph_alt_img#}" value="{$page.content[{$id}].alt_img}" />
                                </div>
                            </div>
                            <div class="col-ph-12 col-md-6">
                                <div class="form-group">
                                    <label for="title_img_{$id}">{#title_img#|ucfirst} :</label>
                                    <input type="text" class="form-control" id="title_img_{$id}" name="content[{$id}][title_img]" placeholder="{#ph_title_img#}" value="{$page.content[{$id}].title_img}" />
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="caption_img_{$id}">{#caption_img#|ucfirst} :</label>
                            <textarea class="form-control" id="caption_img_{$id}" name="content[{$id}][caption_img]" placeholder="{#ph_caption_img#}" cols="65" rows="3">{$page.content[{$id}].caption_img}</textarea>
                        </div>
                    </fieldset>
                </div>
            {/foreach}
        </div>
        <fieldset>
            <legend>Enregistrer</legend>
            {if $edit}
                <input type="hidden" name="thematic[id]" value="{$page.id_tc}" />
            {/if}
            <button class="btn btn-main-theme" type="submit" name="action" value="edit">{#save#|ucfirst}</button>
        </fieldset>
    </form>
    <div class="col-ph-12 col-md-6 col-lg-7">
        <h3>Tailles disponibles</h3>
        <div class="block-img">
            {if $page.imgSrc != null}
                {include file="brick/img.tpl"}
            {/if}
        </div>
    </div>
</div>