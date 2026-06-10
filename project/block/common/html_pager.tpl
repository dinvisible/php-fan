{% if showIfOnePage|default(null) or iPageQtt|default(0) > 1 %}
    {% if tplType == "references" %}
        <div class="pager pgRef">
            {{ oBlock.getEmbeddedForm() }}
            <div>
                {% for pageGr in aPages %}
                    {% for iPage in pageGr %}
                        {% if iPage != iCurrentPage %}
                            <a href="{{ oBlock.getPageUri(iPage) }}">{{ iPage }}</a>
                        {% else %}
                            <b>{{ iPage }}</b>
                        {% endif %}
                    {% endfor %}
                    {% if not loop.last %}<span>...</span>{% endif %}
                {% endfor %}
            </div>
        </div>
    {% endif %}

    {% if tplType == "referencesNL" %}
        <div class="pager pgRef">
            {{ oBlock.getEmbeddedForm() }}
            <div>
                {% if iCurrentPage > 1 %}
                    <a href="{{ oBlock.getPageUri(1) }}">&lt;&lt;</a>
                    <a href="{{ oBlock.getPageUri(iCurrentPage - 1) }}">&lt;</a>
                {% else %}
                    <i>&lt;&lt;</i>
                    <i>&lt;</i>
                {% endif %}
                    {% for iPage in aPagesNL %}
                        {% if iPage != iCurrentPage %}
                            <a href="{{ oBlock.getPageUri(iPage) }}">{{ iPage }}</a>
                        {% else %}
                            <b>{{ iPage }}</b>
                        {% endif %}
                    {% endfor %}
                {% if iCurrentPage < iPageQtt %}
                    <a href="{{ oBlock.getPageUri(iCurrentPage + 1) }}">&gt;</a>
                    <a href="{{ oBlock.getPageUri(iPageQtt) }}">&gt;&gt;</a>
                {% else %}
                    <i>&gt;</i>
                    <i>&gt;&gt;</i>
                {% endif %}
            </div>
        </div>
    {% endif %}

    {% if tplType == "images" %}
        <div class="pager pgImg">
            {{ oBlock.getEmbeddedForm() }}
            <div>
                {% for pageGr in aPages %}
                    {% for iPage in pageGr %}
                        {% if iPage != iCurrentPage %}
                            <a href="{{ oBlock.getPageUri(iPage) }}"><img src="/image/pager/{{ iPage }}.gif" alt="{{ iPage }}" /></a>
                        {% else %}
                            <img src="/image/pager/c{{ iPage }}.gif" alt="{{ iPage }}" />
                        {% endif %}
                    {% endfor %}
                    {% if not loop.last %}<span>...</span>{% endif %}
                {% endfor %}
            </div>
        </div>
    {% endif %}

    {% if tplType == "prev_next" %}
        <div class="pager pgPnRef">
            {{ oBlock.getEmbeddedForm() }}
            <div>
                {% if 1 != iCurrentPage %}
                    <a href="{{ oBlock.getPageUri(iCurrentPage - 1) }}" class="pgPrev">&lt;&lt; prev</a>
                {% endif %}
                {% if iPageQtt != iCurrentPage %}
                    <a href="{{ oBlock.getPageUri(iCurrentPage + 1) }}" class="pgNext">next &gt;&gt;</a>
                {% endif %}
            </div>
        </div>
    {% endif %}

    {% if tplType == "prev_next_ru" %}
        <div class="pager pgPnRef">
            {{ oBlock.getEmbeddedForm() }}
            <div>
                {% if 1 != iCurrentPage %}
                    <a href="{{ oBlock.getPageUri(iCurrentPage - 1) }}" class="pgPrev">&lt;&lt; Предыдущая</a>
                {% endif %}
                {% if iPageQtt != iCurrentPage %}
                    <a href="{{ oBlock.getPageUri(iCurrentPage + 1) }}" class="pgNext">Следующая &gt;&gt;</a>
                {% endif %}
            </div>
        </div>
    {% endif %}

    {% if tplType == "prev_next_ml" %}
        <div class="pager pgPnRef">
            {{ oBlock.getEmbeddedForm() }}
            <div>
                {% if 1 != iCurrentPage %}
                    <a href="{{ oBlock.getPageUri(iCurrentPage - 1) }}" class="pgPrev">&lt;&lt; {{ msg('LINK_PREV') }}</a>
                {% endif %}
                {% if iPageQtt != iCurrentPage %}
                    <a href="{{ oBlock.getPageUri(iCurrentPage + 1) }}" class="pgNext">{{ msg('LINK_NEXT') }} &gt;&gt;</a>
                {% endif %}
            </div>
        </div>
    {% endif %}

    {% if tplType == "prev_nextimage" %}
        <div class="pager pgPnImg">
            {{ oBlock.getEmbeddedForm() }}
            <div>
                {% if 1 != iCurrentPage %}
                    <a href="{{ oBlock.getPageUri(iCurrentPage - 1) }}" class="pgPrev"><img src="/image/pager/prev.gif" alt="previous page" /></a>
                {% endif %}
                {% if iPageQtt != iCurrentPage %}
                    <a href="{{ oBlock.getPageUri(iCurrentPage + 1) }}" class="pgNext"><img src="/image/pager/next.gif" alt="next page" /></a>
                {% endif %}
            </div>
        </div>
    {% endif %}

{% endif %}
