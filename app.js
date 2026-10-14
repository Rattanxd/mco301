class WatchlistManager {
  constructor() {
    this.movies = [];
    this.currentFilter = 'All'; 
  }

  addMovie(title, genre, year) {
    const movie = {
      id: Date.now(),
      title: title,
      genre: genre,
      year: parseInt(year, 10),
      status: "Plan to Watch", 
      rating: 0
    };
    this.movies.push(movie);
    return movie;
  }

  updateMovie(id, newTitle, newGenre, newYear) {
    const movie = this.movies.find(m => m.id === id);
    if (movie) {
      movie.title = newTitle;
      movie.genre = newGenre;
      movie.year = parseInt(newYear, 10);
    }
  }

  markAsWatched(id, rating = 5) {
    const movie = this.movies.find(m => m.id === id);
    if (movie) {
      movie.status = "Watched";
      movie.rating = Math.min(Math.max(rating, 1), 5); 
    }
  }

  deleteMovie(id) {
    this.movies = this.movies.filter(m => m.id !== id);
  }

  getFilteredMovies() {
    if (this.currentFilter === 'All') {
      return this.movies;
    }
    return this.movies.filter(m => m.status === this.currentFilter);
  }
}

const manager = new WatchlistManager();
const movieForm = document.getElementById('movieForm');
const movieList = document.getElementById('movieList');
const filterBtns = document.querySelectorAll('.filter-btn');
const addBtn = document.getElementById('addBtn');

let editingId = null;

movieForm.addEventListener('submit', (e) => {
  e.preventDefault(); 
  
  const title = document.getElementById('titleInput').value.trim();
  const genre = document.getElementById('genreInput').value.trim();
  const year = document.getElementById('yearInput').value;

  if (title && genre && year) {
    if (editingId !== null) {
      manager.updateMovie(editingId, title, genre, year);
      editingId = null;
      addBtn.innerText = '+ Add to Watchlist';
    } else {
      manager.addMovie(title, genre, year);
    }
    
    movieForm.reset(); 
    render();          
  }
});

filterBtns.forEach(btn => {
  btn.addEventListener('click', () => {
    filterBtns.forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    manager.currentFilter = btn.dataset.filter;
    render();
  });
});

function render() {
  movieList.innerHTML = ''; 
  
  const filtered = manager.getFilteredMovies();

  if (filtered.length === 0) {
    movieList.innerHTML = '<li style="text-align: center; color: #a8a8b3; padding: 12px;">No movies found.</li>';
    return;
  }

  filtered.forEach(movie => {
    const li = document.createElement('li');
    const isWatched = movie.status === 'Watched';
    li.className = `movie-card ${isWatched ? 'watched' : ''}`;
    
    li.innerHTML = `
      <div class="movie-info">
        <h3>${movie.title} (${movie.year})</h3>
        <p>${movie.genre}</p>
        <span class="tag">${movie.status}${isWatched && movie.rating > 0 ? ` • ⭐ ${movie.rating}/5` : ''}</span>
      </div>
      <div class="actions">
        ${!isWatched ? `<button class="action-btn watch-btn" onclick="handleWatch(${movie.id})">✓ Watched</button>` : ''}
        <button class="action-btn edit-btn" onclick="handleEdit(${movie.id})">✎ Edit</button>
        <button class="action-btn delete-btn" onclick="handleDelete(${movie.id})">✕</button>
      </div>
    `;

    movieList.appendChild(li);
  });
}

window.handleEdit = (id) => {
  const movie = manager.movies.find(m => m.id === id);
  if (movie) {
    document.getElementById('titleInput').value = movie.title;
    document.getElementById('genreInput').value = movie.genre;
    document.getElementById('yearInput').value = movie.year;
    
    editingId = movie.id;
    addBtn.innerText = '✓ Update Movie';
    
    window.scrollTo({ top: 0, behavior: 'smooth' });
  }
};

window.handleWatch = (id) => {
  const ratingInput = prompt("Rate this movie (1 to 5 stars):", "5");
  if (ratingInput !== null) {
    const rating = parseInt(ratingInput, 10) || 5;
    manager.markAsWatched(id, rating);
    render();
  }
};

window.handleDelete = (id) => {
  manager.deleteMovie(id);
  render();
};

render();