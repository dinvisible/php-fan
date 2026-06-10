{% if rows|default(null) %}
    <table class="formDataTable">
        <tbody>
        {% for row in rows %}
            <tr><td class="formDataLabel">{{ row.label }}:</td><td class="formDataInput">{{ '{' }}{{ row.type }}-{{ row.field }}{{ '}' }}</td></tr>
        {% endfor %}
        </tbody>
    </table>
{% endif %}
