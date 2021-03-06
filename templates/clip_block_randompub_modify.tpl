
<div class="z-formrow">
    <label for="randompub_pubtype">{gt text='Publication type'}</label>
    {html_options id="randompub_pubtype" name='tid' options=$pubtypes selected=$vars.tid}
</div>

<div class="z-formrow">
    <label for="randompub_filter">{gt text='Filter to use'}</label>
    <input id="randompub_filter" name="filter" type="text" size="30" value="{$vars.filter|safetext}" />
</div>

<div class="z-formrow">
    <label for="randompub_template">{gt text='Template'}</label>
    <input id="randompub_template" name="template" type="text" size="30" maxlength="255" value="{$vars.template|safetext}" />
    {gt text='Template' assign='tpl'}
    {gt text='FOLDER' assign='set'}
    {assign var='tpl' value=$tpl|strtoupper}
    {assign var='tpl1' value="$set/random_$tpl.tpl"}
    {assign var='tpl2' value="$set/random_pub.tpl"}
    <em class="z-formnote">
        {gt text="The block will use the template <var>%s</var> if available, or <var>%s</var> if empty." tag1=$tpl1 tag2=$tpl2}
        {if $vars.tid}
            {modurl modname='Clip' type='admin' func='generator' tid=$vars.tid code='blockpub' assign='codeurl'}
            <br />{gt text='You can customize the generic code <a href="%s">available here</a>.' tag1=$codeurl|safetext}
        {/if}
        <br />{gt text='If the template is not found, Clip will use generic_blockpub.tpl if the development mode is enabled.'}
    </em>
</div>

<div class="z-formrow">
    <label for="randompub_cachelt">{gt text='Cache lifetime in seconds'}</label>
    <input id="randompub_cachelt" name="cachelifetime" type="text" size="30" maxlength="255" value="{$vars.cachelifetime|safetext}" />
</div>
