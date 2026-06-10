<table>
    <tbody>
        <tr><td><table class="multiRowDataTable">
            <tbody>
            {% if isHead|default(null) %}
            <tr>
                {% if showId|default(null) %}<th>Id</th>{% endif %}
                {% if columns|default(null) %}
                    {% for col in columns %}
                        <th{% if aOpRight[col.field]|default(null) %} colspan="2"{% endif %}{% if col.width|default(null) %} style="width:{{ col.width }}px;"{% endif %}>
                            {% if col.width|default(null) %}
                                <img src="image/1x1.gif" width="{{ col.width }}" class="headSpacer" />
                            {% endif %}
                            {% if hdOrder[col.field] is defined %}
                                {{ '{' }}tbl_order-{{ col.field }}{{ '}' }}
                            {% else %}
                                {{ col.head }}
                            {% endif %}
                        </th>
                    {% endfor %}
                {% endif %}
                {% if showDel|default(null) %}<th class="del">Del</th>{% endif %}
            </tr>
            {% endif %}
            [<tr class="row{{ '{' }}zebra{{ '}' }}">
                {% if showId|default(null) %}
                    <td class="id">{{ '{' }}id-id{{ '}' }}</td>
                {% endif %}
                {% if columns|default(null) %}
                    {% for col in columns %}
                        {% set opr = aOpRight[col.field]|default(null) %}
                        {% if opr and opr.pos != "after" %}
                            <td class="openRight_before">{{ '{' }}{{ opr.pat }}-{{ col.field }}{{ '}' }}</td>
                        {% endif %}
                        <td>{{ '{' }}{{ col.type }}-{{ col.field }}{{ '}' }}</td>
                        {% if opr and opr.pos == "after" %}
                            <td class="openRight_after">{{ '{' }}{{ opr.pat }}-{{ col.field }}{{ '}' }}</td>
                        {% endif %}
                    {% endfor %}
                {% endif %}
                {% if showDel|default(null) %}
                    <td class="del">{{ '{' }}delete_1{{ '}' }}</td>
                {% endif %}
            </tr>]
            </tbody>
        </table></td></tr>
        {% if bHideTotal|default(null) is empty %}
            <tr><td>
                <div class="total_qtt"><span>Получено:</span> <i>{{ '{' }}total_qtt{{ '}' }}</i> записей.</div>
            </td></tr>
        {% endif %}
    </tbody>
</table>
