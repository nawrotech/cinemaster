import { Autocomplete } from 'stimulus-autocomplete'


export default class CustomAutocomplete extends Autocomplete {

        static values = {
            screeningFormatsForMovieUrl: String,
            movieScreeningFormatDeleteUrl: String,
            movieScreeningFormatCreateUrl: String,
            deleteCsrfToken: String,
            addCsrfToken: String,
        }
    
        static targets = ["screeningFormatsForMovie"];
    
        static classes = ["deleteButton"]
    
        connect() {
            super.connect();
            this.element.addEventListener("autocomplete.change", this.handleChangeEvent.bind(this));
            this.fetchMovieScreeningFormats();  

            console.log(this.addCsrfTokenValue);
            console
        }

        disconnect() {
            this.element.removeEventListener("autocomplete.change", this.handleChangeEvent)
            super.disconnect()
        }
    
        fetchMovieScreeningFormats() {
            fetch(this.screeningFormatsForMovieUrlValue, 
                )
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
                if (!Array.isArray(data) || data.length === 0) {
                    this.screeningFormatsForMovieTarget.innerHTML = '';
                    return;
                }  

                this.screeningFormatsForMovieTarget.innerHTML = data.map((msf) => 
                    this.createMovieScreeningFormatListItem(msf)
                ).join("");         
          }
          

          createMovieScreeningFormatListItem(movieScreeningFormat) {
            return `<li>
                        <input type="hidden" value=${movieScreeningFormat.id} name="screeningFormats[]" />
                        ${movieScreeningFormat.displayScreeningFormat} 
                        ${!movieScreeningFormat.isScheduledShowtime ?  
                            `<button class="${this.deleteButtonClasses.join(" ")}" type="button" value="${movieScreeningFormat.id}" 
                                data-action="movie-screening-format-ajaxes#deleteMovieScreeningFormat">
                        Delete</button>` : ""}
                    </li>`
          }

    
          handleChangeEvent(event) {
            const screeningFormatId = event.detail.value;
            if (!screeningFormatId) {
                return;
            }
            this.addMovieScreeningFormat(screeningFormatId);
            this.inputTarget.select()
          }
        
    
          deleteMovieScreeningFormat(event) {
                const movieScreeningFormatId = event.currentTarget.value;
                fetch(`${this.movieScreeningFormatDeleteUrlValue}/${movieScreeningFormatId}`, {
                    method: "DELETE",
                    headers: {
                        "X-CSRF-Token": this.deleteCsrfTokenValue,
                        "Content-Type": "application/json",
                    },
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
                method: "POST",
                headers: {
                    "X-CSRF-Token": this.addCsrfTokenValue,
                    "Content-Type": "application/json",
                },
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

