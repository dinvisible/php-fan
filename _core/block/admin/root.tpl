<!DOCTYPE html>
<html lang="en">
<head>
<title>{{ title|default('') }}</title>
{{ headBefore|default('') }}
{#

====== meta-tags ====== #}
{% if meta|default(null) %}{% for meta_item in meta %}
<meta{% if meta_item.name|default(null) %} name="{{ meta_item.name }}"{% endif %} content="{{ meta_item.content }}"{% if meta_item.http_equiv|default(null) %} http-equiv="{{ meta_item.http_equiv }}"{% endif %}{% if meta_item.scheme|default(null) %} scheme="{{ meta_item.scheme }}"{% endif %}{% if meta_item.id|default(null) %} id="{{ meta_item.id }}"{% endif %} />
{% endfor %}{% endif %}
{#

====== CSS (old format) - block ====== #}
{% if externalCss.old|default(null) %}{% for CssOld in externalCss.old %}
<link rel="stylesheet" type="text/css" href="{{ CssOld }}"></link>
{% endfor %}{% endif %}
{#

====== CSS - block ====== #}
{% if externalCss.new|default(null) or embedCss|default(null) %}
<style type="text/css">
<!--/*--><![CDATA[/*><!--*/
{% if externalCss.new|default(null) %}{% for cssFile in externalCss.new %}
@import url({{ cssFile }});
{% endfor %}{% endif %}
{{ embedCss|default('') }}
/*]]>*/-->
</style>
{% endif %}
{#

====== CSS (IE) - block ====== #}
{% if externalCss.ie|default(null) %}
<style type="text/css">
{% for cssFile in externalCss.ie %}
@import url({{ cssFile }});
{% endfor %}
</style>
{% endif %}
{#

====== External head JavaScript ====== #}
{% if externalJS.head|default(null) %}{% for jsFile in externalJS.head %}
<script type="text/javascript" src="{{ jsFile }}"></script>
{% endfor %}{% endif %}
{#

====== Embeded head JavaScript ====== #}
{% if embedJS.head|default(null) %}
<script type="text/javascript">
<!--//--><![CDATA[//><!--
{{ embedJS.head[0]|default('') }}{{ embedJS.head[1]|default('') }}{{ embedJS.head[2]|default('') }}
//--><!]]>
</script>
{% endif %}
{{ headAfter|default('') }}
</head>
<body{% if bodyClass|default(null) %} class="{{ bodyClass }}"{% endif %}>{% if carcass|default(null) %}{{ carcass }}{% else %}{{ main|default('') }}{% endif %}
{#

====== External body JavaScript (deprecated!!!) ====== #}
{% if externalJS.body|default(null) %}{% for jsFile in externalJS.body %}
<script type="text/javascript" src="{{ jsFile }}"></script>
{% endfor %}{% endif %}
{#

====== Embeded body JavaScript (is not advisable) ====== #}
{% if embedJS.body|default(null) %}
<script type="text/javascript">
<!--//--><![CDATA[//><!--
{{ embedJS.body[0]|default('') }}{{ embedJS.body[1]|default('') }}{{ embedJS.body[2]|default('') }}
//--><!]]>
</script>
{% endif %}
{% if poweredBy|default(null) %}<!--
Powered by: {{ poweredBy }}. Copyright (C) 2005-2012 Alexandr Nosov, http://www.alex.4n.com.ua/, Kharkov.
PHP-FAN is licensed under the terms of the GNU Lesser General Public License: http://www.opensource.org/licenses/lgpl-license.php
-->{% endif %}
</body></html>
