<h2>{{ error|default(null) is empty ? 'Error 500' : error }}</h2>
<p>{{ message|default(null) is empty ? 'Internal system error' : message }}</p>
