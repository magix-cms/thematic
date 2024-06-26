{extends file="layout.tpl"}
{block name="title"}{$thematic.seo.title}{/block}
{block name="description"}{$thematic.seo.description}{/block}
{block name='body:id'}thematic{/block}
{block name="styleSheet" nocache}
    {$css_files = ["thematic","gallery","lightbox","slider"]}
{/block}

{block name='article'}
    <article class="container" itemprop="mainContentOfPage">
        {block name='article:content'}
            {*<pre>{print_r($thematic)}</pre>*}
            <h1 itemprop="name">{$thematic.name}</h1>
            <div itemprop="text" class="clearfix">
                {include file="img/loop/gallery.tpl" imgs=$thematic.imgs}
                {$thematic.content}
            </div>
            {*<div class="text clearfix" itemprop="text">
                {if isset($thematic.img.name)}
                    <a href="{$thematic.img.large.src}" class="img-zoom img-float float-right" title="{$thematic.img.title}" data-caption="{$thematic.img.caption}">
                        <figure>
                            {include file="img/img.tpl" img=$thematic.img lazy=true}
                            {if $thematic.img.caption}
                                <figcaption>{$thematic.img.caption}</figcaption>
                            {/if}
                        </figure>
                    </a>
                {/if}
                {$thematic.content}
            </div>*}
            {if !empty($thematics)}
            <div class="vignette-list">
                <div class="section-block">
                    <div class="row row-center" itemprop="mainEntity" itemscope itemtype="http://schema.org/ItemList">
                        {include file="thematic/loop/thematic.tpl" data=$thematics small='true' classCol='vignette col-12 col-xs-8 col-sm-6 col-md-4 col-xl-3'}
                    </div>
                </div>
            </div>
            {/if}
        {/block}
    </article>
{/block}