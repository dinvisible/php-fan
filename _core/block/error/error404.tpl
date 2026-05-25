<h2>{{ error|default(null) is empty ? 'Error 404' : error }}</h2>
<p>{{ message|default(null) is empty ? "File isn't found" : message }}</p>
