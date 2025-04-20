import { Controller } from '@hotwired/stimulus';
import { dateTimeObjectConverter, timeDisplayConverter } from '../utils/showtime-utils.js';

/* stimulusFetch: 'lazy' */
export default class extends Controller {
    static targets = ['dayAxis', "currentDate", "showtime", "hour", 'date']
    static values = {
        showtimesUrl: String,
        showtimeEditUrl: String,
        showtimeStartsAtDate: String
    }

    static classes = ['showtime']

    showtimes = [];

    connect() {
        console.log(this.showtimeClass);

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
        showtimeElement.setAttribute("data-showtime-axis-target", "showtime");
        showtimeElement.innerHTML = `
            <p class="m-0">${timeDisplayConverter(showtime.startsAt)} - ${timeDisplayConverter(showtime.endsAt)}</p>
            <h2>${showtime.movieTitle}</h2>
            <p class="m-0">Format: ${showtime.screeningFormat}</p>
            <a href="${this.showtimeEditUrlValue}/${showtime.id}">Edit</a>
        `;

        showtimeElement.classList.add(this.showtimeClass);
        showtimeElement.style.setProperty("--showtimeDuration", `${showtime.durationInMinutes}px`);
        showtimeElement.style.setProperty("--minutesPastHour", `${showtimeDateObject.minute}px`);

        return showtimeElement;
    }

  
}
