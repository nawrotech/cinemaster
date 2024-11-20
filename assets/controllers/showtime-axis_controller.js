import { Controller } from '@hotwired/stimulus';
import { dateTimeObjectConverter, timeDisplayConverter } from '../utils/showtime-utils.js';

/* stimulusFetch: 'lazy' */
export default class extends Controller {
    static targets = ['dayAxis', "currentDate"]
    static values = {
        showtimesUrl: String,
        showtimeEditUrl: String,
        showtimeStartsAt: String
    }

    showtimes = [];
    showtimeElementClassName = "showtime";
    hourElements = Array.from(this.dayAxisTarget.children);


    connect() {
       this.fetchShowtimes(this.showtimeStartsAtValue);
       this.currentDateTarget.textContent = this.showtimeStartsAtValue;

    }

    pickDate(event) {
        const dateTime = event.currentTarget.value.split("T");
        const pickedDate = dateTime.at(0);
        this.currentDateTarget.textContent = pickedDate;
        this.fetchShowtimes(pickedDate);
    }

    clearShowtimeElements() {
        this.hourElements.forEach(el => {
            const showtimeElements = el.querySelectorAll(`.${this.showtimeElementClassName}`);
            showtimeElements.forEach(showtime => showtime.remove());
        });
    }

    fetchShowtimes(date) {
        fetch(`${this.showtimesUrlValue}/${date}`)
            .then(res => res.json())
            .then(data => {
                this.clearShowtimeElements();
                this.showtimes = data;
                this.renderShowtimes();
            });
    }

    renderShowtimes() {
        this.showtimes.forEach(showtime => {

            const showtimeDateObject = dateTimeObjectConverter(showtime.startsAt);
            const hourEl = this.hourElements.find(el => Number(el.dataset.hour) === showtimeDateObject.hour);

            const showtimeElement = this.createShowtimeElement(showtime);
            hourEl.appendChild(showtimeElement);
            
        });
    }


    createShowtimeElement(showtime) {
        const showtimeDateObject = dateTimeObjectConverter(showtime.startsAt);

        const showtimeElement = document.createElement("div");
        showtimeElement.innerHTML = `
            <p class="m-0">${timeDisplayConverter(showtime.startsAt)} - ${timeDisplayConverter(showtime.endsAt)}</p>
            <h2>${showtime.movieTitle}</h2>
            <p class="m-0">Format: ${showtime.screeningFormat}</p>
            <a href="${this.showtimeEditUrlValue}/${showtime.id}">Edit</a>
        `;
        showtimeElement.className = "showtime";
        showtimeElement.style.setProperty("--showtimeDuration", `${showtime.durationInMinutes}px`);
        showtimeElement.style.setProperty("--minutesPastHour", `${showtimeDateObject.minute}px`);

        return showtimeElement;
    }

  
}
