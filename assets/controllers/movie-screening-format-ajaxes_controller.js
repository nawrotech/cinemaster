import { Autocomplete } from 'stimulus-autocomplete'


export default class CustomAutocomplete extends Autocomplete {

        static targets = ["screeningFormatsForMovie"];
    
        static values = {
            screeningFormatsForMovieUrl: String,
            movieScreeningFormatDeleteUrl: String,
            movieScreeningFormatCreateUrl: String,
        }
        
        storedValues = [];
        connect() {
            super.connect();
            this.element.addEventListener("autocomplete.change", this.handleChangeEvent.bind(this));
            this.fetchMovieScreeningFormats();
                
          }
    
          fetchMovieScreeningFormats() {
            fetch(this.screeningFormatsForMovieUrlValue)
                .then(res => res.json())
                .then(data => {
                    const movieScreeningFormatsList = data.map((el) => {
                        return  `<li>
                                    <input type="hidden" value=${el.id} name="screeningFormats[]" />
                                    ${el.movieScreeningFormatName} 
                                    <button class="btn btn-danger btn-sm" type="button" value="${el.id}" data-action="movie-screening-format-ajaxes#deleteMovieScreeningFormat" >Delete</button>
                                </li>`
                    });
                    const screeningFormatsForMovieListElements = movieScreeningFormatsList.join("");
                    this.screeningFormatsForMovieTarget.innerHTML = screeningFormatsForMovieListElements;         
                });
          }
          
    
          handleChangeEvent(event) {
            const screeningFormatId = event.detail.value;
            this.createMovieScreeningFormat(screeningFormatId);
            this.inputTarget.select()
          }
        
    
          deleteMovieScreeningFormat(event) {
                const movieScreeningFormatId = event.currentTarget.value;
                fetch(`${this.movieScreeningFormatDeleteUrlValue}/${movieScreeningFormatId}`, {
                    method: "DELETE"
                }).then(() => this.fetchMovieScreeningFormats());    
          }
    
          createMovieScreeningFormat(screeningFormatId) {
            fetch(`${this.movieScreeningFormatCreateUrlValue}/${screeningFormatId}`, {
                method: "POST"
            }).then(() => this.fetchMovieScreeningFormats());
          }
    
    
    
      }

