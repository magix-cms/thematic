{extends file="layout.tpl"}
{block name="title"}{$root.seo.title}{/block}
{block name="description"}{$root.seo.description}{/block}
{block name='body:id'}thematics{/block}
{block name="styleSheet" nocache}
    {$css_files = ["thematic","gallery","lightbox","slider"]}
{/block}

{block name='article'}
    <article class="container" itemprop="mainContentOfPage">
        {block name='article:content'}
            <h1 itemprop="name">{$root.name}</h1>
            <div itemprop="text" class="clearfix">
                {$root.content}
            </div>
            {*<pre>{print_r($thematics)}</pre>*}
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