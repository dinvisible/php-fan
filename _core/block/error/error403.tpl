<h2>{{ error|default(null) is empty ? 'Error 403' : error }}</h2>
<p>{{ message|default(null) is empty ? "File isn't accessible" : message }}</p>
