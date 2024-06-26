
const options = {
    method: 'GET', 
    headers: {
        accept: 'application/json',
        Authorization: 'Bearer eyJhbGciOiJIUzI1NiJ9.eyJhdWQiOiJhYTJjYTAwZDYxZWIzOTEyYjZlNzc4MDA4YWQ3ZmNjOCIsInN1YiI6IjYyODJmNmYwMTQ5NTY1MDA2NmI1NjlhYyIsInNjb3BlcyI6WyJhcGlfcmVhZCJdLCJ2ZXJzaW9uIjoxfQ.4MJSPDJhhpbHHJyNYBtH_uCZh4o0e3xGhZpcBIDy-Y8'
    }
};

const apiUrl = 'https://api.themoviedb.org/3/movie/popular?language=en-US&page=1';

async function fetchMovies() {
    try {
        const response = await fetch(apiUrl, options);
        console.log('Response status:', response.status);
        if (!response.ok) {
            throw new Error(`Network response was not ok: ${response.statusText}`);
        }
        const data = await response.json();
        console.log('Fetched data:', data);
        displayMovies(data.results);
    } catch (error) {
        console.error('Error fetching data:', error);
    }
}

function displayMovies(movies) {
    const moviesContainer = document.getElementById('movies');
    moviesContainer.innerHTML = '';

    movies.forEach(movie => {
        const movieElement = document.createElement('div');
        movieElement.classList.add('movie');

        const moviePoster = document.createElement('img');
        moviePoster.src = `https://image.tmdb.org/t/p/w200${movie.poster_path}`;
        moviePoster.alt = movie.title;

        const movieDetails = document.createElement('div');
        movieDetails.classList.add('movie-details');

        const movieTitle = document.createElement('h2');
        movieTitle.textContent = movie.title;

        const movieOverview = document.createElement('p');
        movieOverview.textContent = movie.overview;

        const separationLine = document.createElement('hr');

        movieDetails.appendChild(movieTitle);
        movieDetails.appendChild(movieOverview);
        movieElement.appendChild(moviePoster);
        movieElement.appendChild(movieDetails);
        moviesContainer.appendChild(movieElement);
        moviesContainer.appendChild(separationLine);
    });
}

fetchMovies();