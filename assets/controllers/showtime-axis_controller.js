import { Controller } from '@hotwired/stimulus';
import { dateTimeObjectConverter, timeDisplayConverter } from '../utils/showtime-utils.js';

/* stimulusFetch: 'lazy' */
export default class extends Controller {
    static targets = ['dayAxis', "currentDate", "showtime", "hour", 'date']
    static values = {
        showtimesUrl: String,
        showtimeEditUrl: String,
        showtimeDeleteUrl: String,
        showtimeStartsAtDate: String,
    }

    static classes = ['showtime']

    showtimes = [];

    connect() {
        const date = this.extractDateFromDateLocalInput(this.dateTarget.value)  
                        ?? this.showtimeStartsAtDateValue;
        this.fetchShowtimes(date);
        this.currentDateTarget.textContent = date;
    }

    extractDateFromDateLocalInput(dateLocalValue) {
        if (!dateLocalValue) {
            return null;
        }

        const dateTime = dateLocalValue.split("T");
        return dateTime.at(0);
    }   

    pickDate(event) {
        const dateTime = event.currentTarget.value.split("T");
        const pickedDate = dateTime.at(0);
        this.currentDateTarget.textContent = pickedDate;
        this.fetchShowtimes(pickedDate);
    }

    clearShowtimeElements() {
        this.showtimeTargets.forEach(showtime => showtime.remove());
    }

    fetchShowtimes(date) {
        fetch(`${this.showtimesUrlValue}/${date}`)
            .then(res =>  {
                if (!res.ok) {
                    throw new Error(`HTTP error ${res.status}: ${res.statusText}`);
                }
                return res.json()
            })
            .then(data => {
                this.clearShowtimeElements();
                this.showtimes = data;
                this.renderShowtimes();
            })
            .catch(error => {
                console.error('Error fetching showtimes:', error);
                this.showtimes = [];
                return [];
              });
    }

    renderShowtimes() {
        this.showtimes.forEach(showtime => {

            const showtimeDateObject = dateTimeObjectConverter(showtime.startsAt);
            const hourEl = this.hourTargets.find(el => Number(el.dataset.hour) === showtimeDateObject.hour);

            if (hourEl) {
                const showtimeElement = this.createShowtimeElement(showtime);
                hourEl.appendChild(showtimeElement);
            }
 
        });
    }



    createShowtimeElement(showtime) {
        const showtimeDateObject = dateTimeObjectConverter(showtime.startsAt);

        const showtimeElement = document.createElement("div");
        showtimeElement.dataset.showtimeId = showtime.id;
        showtimeElement.setAttribute("data-showtime-axis-target", "showtime");
        showtimeElement.innerHTML = `
            <p class="m-0 fs-5">${timeDisplayConverter(showtime.startsAt)} - ${timeDisplayConverter(showtime.endsAt)}</p>
            <h2 class="m-0 fs-5">${showtime.movieTitle}</h2>
            <p class="m-0 fs-6">Format: ${showtime.screeningFormat}</p>
            <a class="link-primary fs-6" href="${this.showtimeEditUrlValue}/${showtime.id}">Edit</a>
            <button class="btn btn-link fs-6 text-danger" 
                    data-showtime-axis-id-param="${showtime.id}"
                    data-action="showtime-axis#deleteShowtime">Delete</button>
        `;

        showtimeElement.classList.add(this.showtimeClass);
        showtimeElement.style.setProperty("--showtimeDuration", `${showtime.durationInMinutes}px`);
        showtimeElement.style.setProperty("--minutesPastHour", `${showtimeDateObject.minute}px`);

        return showtimeElement;
    }

    deleteShowtime(event) {
        event.preventDefault();
        const showtimeId = event.params.id;
        const showtimeElement = this.showtimeTargets.find(el => Number(el.dataset.showtimeId) === showtimeId);

        if (!confirm('Are you sure you want to delete this showtime?')) {
            return;
        }
        
        fetch(`${this.showtimeDeleteUrlValue}/${showtimeId}`, {
            method: 'DELETE',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(async response => {
            let data;

            try {
                data = await response.json(); 
            } catch (e) {
               data = { message: response.message || `HTTP Error ${response.status}` };
            }
           
            if (!response.ok) {
                throw new Error(data.message || `Request failed with status ${response.status}`);
            }
            return data;
        })
        .then(data => {
            if (showtimeElement) {
                showtimeElement.remove();
            }
            
            const successMessage = document.createElement('div');
            successMessage.classList.add('alert', 'alert-success', 'position-fixed', 'top-0', 'start-50', 'translate-middle-x');
            successMessage.textContent = data.message;
            document.body.appendChild(successMessage);
            
            setTimeout(() => {
                successMessage.remove();
            }, 3000);
        })
        .catch(error => {
            const errorMessage = document.createElement('div');
            errorMessage.classList.add('alert', 'alert-danger', 'position-fixed', 'top-0', 'start-50', 'translate-middle-x');
            errorMessage.textContent = error.message || 'An error occurred while deleting the showtime';
            document.body.appendChild(errorMessage);
            
            setTimeout(() => {
                errorMessage.remove();
            }, 3000);
        });
    }

  
}
