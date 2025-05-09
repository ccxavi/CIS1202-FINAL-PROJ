<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Search Articles</title>
  <style>
    img { max-width: 300px; display: block; margin-bottom: 10px; }
    .article { margin-bottom: 30px; border-bottom: 1px solid #ccc; padding-bottom: 10px; }
  </style>
</head>
<body>

  <h1>Search & Filter Articles</h1>

  <form method="GET" action="searchResults.php" id="filterForm" onsubmit="event.preventDefault(); fetchArticles();">
    <label for="topic">Filter by Topic:</label>
    <select name="topic" id="topic">
        <option value="">Select Topic</option>
        <option value="Health">Health</option>
        <option value="Sports">Sports</option>
        <option value="Science">Science</option>
    </select>

    <select name="source_type" id="source_type">
        <option value="">Select Source Type</option>
        <option value="News">News</option>
        <option value="Journals">Journals</option>
        <option value="Blogs">Blogs</option>
        <option value="Databases">Databases</option>
    </select>

    <select name="credibility" id="credibility">
        <option value="">Select Credibility</option>
        <option value="Verified">Verified</option>
        <option value="Peer-reviewed">Peer-reviewed</option>
        <option value="User-added">User-added</option>
    </select>

    <select name="region" id="region">
        <option value="">Select Region</option>
        <option value="Asia">Asia</option>
        <option value="Europe">Europe</option>
        <option value="North America">North America</option>
    </select>

    <input type="text" name="date_range" id="date_range" placeholder="YYYY-MM-DD:YYYY-MM-DD" />

    <button type="submit">Apply Filters</button>
  </form>

  <br>

  <input type="text" id="searchInput" placeholder="Search description...">
  <button onclick="fetchArticles()">Search</button>

  <hr>

  <div id="results"></div>

  <script>
    async function fetchArticles() {
      const topic = document.getElementById('topic').value;
      const source_type = document.getElementById('source_type').value;
      const credibility = document.getElementById('credibility').value;
      const region = document.getElementById('region').value;
      const date_range = document.getElementById('date_range').value;
      const query = document.getElementById('searchInput').value;

      let url = '/CIS1202-FINAL-PROJ-2/api/articles.php';
      const params = new URLSearchParams();

      if (topic) params.append('topic', topic);
      if (source_type) params.append('source_type', source_type);
      if (credibility) params.append('credibility', credibility);
      if (region) params.append('region', region);
      if (date_range) params.append('date_range', date_range);
      if (query) params.append('description', query); // Assuming your API allows searching descriptions

      if ([...params].length > 0) {
        url += '?' + params.toString();
      }

      try {
        const res = await fetch(url);
        const json = await res.json();

        const resultsDiv = document.getElementById('results');
        resultsDiv.innerHTML = '';

        if (json.status === 'success' && json.data.length > 0) {
          json.data.forEach(article => {
            const div = document.createElement('div');
            div.className = 'article';
            div.innerHTML = `
              <img src="${article.preview_image_link}" alt="Preview">
              <p><strong>Link:</strong> <a href="${article.article_link}" target="_blank">${article.article_link}</a></p>
              <p><strong>Description:</strong> ${article.description}</p>
            `;
            resultsDiv.appendChild(div);
          });
        } else {
          resultsDiv.innerHTML = `<p>No articles found.</p>`;
        }
      } catch (error) {
        document.getElementById('results').innerHTML = `<p>Error fetching articles.</p>`;
      }
    }

    // Fetch on page load with any pre-set query parameters
    window.onload = () => {
      fetchArticles();
    };
  </script>

</body>
</html>
