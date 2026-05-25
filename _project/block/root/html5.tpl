<!DOCTYPE html>
<html lang="en">
<head>
<title>{{ title|default('') }}</title>
</head>
<body>{% if carcass|default(null) %}{{ carcass }}{% else %}{{ main|default('') }}{% endif %}
{% if poweredBy|default(null) %}<!--
Powered by: {{ poweredBy }}. Copyright (C) 2005-2014 Alexandr Nosov, http://www.alex.4n.com.ua/, Kharkov.
PHP-FAN is licensed under the terms of the GNU Lesser General Public License: http://www.opensource.org/licenses/lgpl-license.php
-->{% endif %}
</body></html>
