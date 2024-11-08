import { startStimulusApp } from '@symfony/stimulus-bundle';

const app = startStimulusApp();
import { Autocomplete } from 'stimulus-autocomplete'

// register any custom, 3rd party controllers here

class CustomAutocomplete extends Autocomplete {

    static targets = ["selectedScreeningFormats"];
    static values = {
        movieScreeningFormatsUrl: String,
        addMovieScreeningFormatUrl: String,
        deleteMovieScreeningFormatUrl: String,
        movieId: Number
        
    }
    
    storedValues = [];
    screeningFormatsForMovie = "";
    connect() {
        super.connect();
        this.element.addEventListener("autocomplete.change", this.handleChangeEvent.bind(this));
        
        console.log(this.deleteMovieScreeningFormatUrlValue);
        // console.log(this.deleteMovieScreeningFormat

        this.fetchMovieScreeningFormats();

        console.log(this.movieIdValue);

      }

      fetchMovieScreeningFormats() {
        fetch(this.movieScreeningFormatsUrlValue)
            .then(res => res.json())
            .then(data => {
                const movieScreeningFormatsList = data.map((el) => {
                    return  `<li>
                                <input type="hidden" value=${el.id} name="screeningFormats[]" />
                                ${el.movieScreeningFormatName} 
                                <button type="button" value="${el.id}" data-action="autocomplete#deleteMovieScreeningFormat" >%target for this to delete on ajax%</button>
                            </li>`
                });
                this.screeningFormatsForMovie = movieScreeningFormatsList.join("");
                this.selectedScreeningFormatsTarget.innerHTML = this.screeningFormatsForMovie;            
            });
      }
      
      

      deleteMovieScreeningFormat(event) {
            const movieScreeningFormatId = event.currentTarget.value;
            fetch(`${this.deleteMovieScreeningFormatUrlValue}/${movieScreeningFormatId}`, {
                method: "DELETE"
            }).then(() => this.fetchMovieScreeningFormats());
           
      }


      handleChangeEvent(event) {
        const screeningFormatId = event.detail.value;

        fetch(`${this.deleteMovieScreeningFormatUrlValue}/${screeningFormatId}`, {
            method: "POST",
            headers: {
                "Content-Type": "application/json"
            },
            body: JSON.stringify({
                movieId: this.movieIdValue
            })
        }).then(() => this.fetchMovieScreeningFormats());


        this.inputTarget.select()


        // if (!this.storedValues.some(detail => detail.value ===  event.detail.value)) {
        //     this.storedValues.push(event.detail);
        // }


        // const htmlElements = this.storedValues.map((el) => {
        //     return `<li>
        //                 <input type="hidden" value=${el.value} name="screeningFormats[]" />
        //                 ${el.textValue} 
        //             </li>`
        // });

        // fetch("movie/")


        // const htmlFragment = htmlElements.join("");

        // this.selectedScreeningFormatsTarget.innerHTML = htmlFragment;

      }
    

  }
  

app.register('autocomplete', CustomAutocomplete);

document.addEventListener("autocomplete:load", function(event) {
    console.log(event);
});