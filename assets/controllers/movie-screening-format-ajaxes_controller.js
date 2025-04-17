import { Autocomplete } from 'stimulus-autocomplete'


export default class CustomAutocomplete extends Autocomplete {

        static targets = ["screeningFormatsForMovie"];
    
        static values = {
            screeningFormatsForMovieUrl: String,
            movieScreeningFormatDeleteUrl: String,
            movieScreeningFormatCreateUrl: String,
        }
        
        connect() {
            super.connect();
            this.element.addEventListener("autocomplete.change", this.handleChangeEvent.bind(this));
            this.fetchMovieScreeningFormats();  
          }

        disconnect() {
        this.element.removeEventListener("autocomplete.change", this.handleChangeEvent)
        super.disconnect()
        }
    
          fetchMovieScreeningFormats() {
            fetch(this.screeningFormatsForMovieUrlValue)
                .then(res => {
                    if (!res.ok) {
                        throw new Error("Network response was not ok");
                    }
                    return res.json();
                })
                .then(data => {
                   this.loadMovieScreeningFormats(data);
                })
                .catch(error => {
                   throw error;
                });
          }

          loadMovieScreeningFormats(data) {
            const movieScreeningFormatsList = data.map((msf) => {
                return  this.createMovieScreeningFormatListItem(msf);
            });
            const screeningFormatsForMovieListElements = movieScreeningFormatsList.join("");
            this.screeningFormatsForMovieTarget.innerHTML = screeningFormatsForMovieListElements;         
          }
          

          createMovieScreeningFormatListItem(movieScreeningFormat) {
            return `<li>
                        <input type="hidden" value=${movieScreeningFormat.id} name="screeningFormats[]" />
                        ${movieScreeningFormat.displayScreeningFormat} 
                        ${!movieScreeningFormat.isScheduledShowtime ?  
                            `<button class="btn btn-danger btn-sm" type="button" value="${movieScreeningFormat.id}" 
                                data-action="movie-screening-format-ajaxes#deleteMovieScreeningFormat">
                        Delete</button>` : ""}
                    </li>`
          }

    
          handleChangeEvent(event) {
            const screeningFormatId = event.detail.value;
            this.addMovieScreeningFormat(screeningFormatId);
            this.inputTarget.select()
          }
        
    
          deleteMovieScreeningFormat(event) {
                const movieScreeningFormatId = event.currentTarget.value;
                fetch(`${this.movieScreeningFormatDeleteUrlValue}/${movieScreeningFormatId}`, {
                    method: "DELETE"
                }).then((res) => {
                    if (!res.ok) {
                        throw new Error("Network response was not ok");
                    }
                    this.fetchMovieScreeningFormats();
                }).catch((error) => {
                    throw error;
                });    
          }
    
          addMovieScreeningFormat(screeningFormatId) {
            fetch(`${this.movieScreeningFormatCreateUrlValue}/${screeningFormatId}`, {
                method: "POST"
            }).then((res) => {
                if (!res.ok) {
                    throw new Error("Network response was not ok");
                }
                this.fetchMovieScreeningFormats();
             
            }).catch((error) => {
                throw error;
            });
          }
    
    
    
      }

