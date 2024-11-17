import { Controller } from '@hotwired/stimulus';

/*
* The following line makes this controller "lazy": it won't be downloaded until needed
* See https://github.com/symfony/stimulus-bridge#lazy-controllers
*/
/* stimulusFetch: 'lazy' */
export default class extends Controller {
    static targets = ['dayAxis']
    static values = {
        showtimesUrl: String,
        showtimeEditUrl: String,
        showtimeStartsAt: String
    }

    showtimes = [];
    hourElements = Array.from(this.dayAxisTarget.children);

    connect() {
       this.fetchShowtimes(this.showtimeStartsAtValue);

    }

    pickDate(event) {
        const dateTime = event.currentTarget.value.split("T");
        const pickedDate = dateTime.at(0);
        this.fetchShowtimes(pickedDate);

    }

    refreshAxis() {
        this.hourElements.forEach(el => {
            const showtimeElements = el.querySelectorAll('.showtime');
            showtimeElements.forEach(showtime => showtime.remove());
        });
    }

    fetchShowtimes(date) {
        fetch(`${this.showtimesUrlValue}/${date}`)
            .then(res => res.json())
            .then(data => {
                this.refreshAxis();
                this.showtimes = data;
                console.log(data);
                this.renderShowtimes();
            });
    }


    timeDisplayConverter(date) {
        const dateObj = new Date(date);
        return `${String(dateObj.getUTCHours()).padStart(2, "0")}:${String(dateObj.getUTCMinutes()).padStart(2, "0")}`;
    }
    
    timeObjectConverter(date) {
        const dateObj = new Date(date);
        return {
            hours: dateObj.getUTCHours(),
            minutes: dateObj.getUTCMinutes()
        };
    }

    renderShowtimes() {
        this.showtimes.forEach(showtime => {

            const showtimeDateObject = this.timeObjectConverter(showtime.startsAt);
            const hourEl = this.hourElements.find(el => el.dataset.hour == showtimeDateObject.hours);

            const showtimeElement = this.createShowtimeElement(showtime);
            hourEl.appendChild(showtimeElement);
            
        });
    }

    createShowtimeElement(showtime) {
        const showtimeDateObject = this.timeObjectConverter(showtime.startsAt);

        const showtimeElement = document.createElement("div");
        showtimeElement.innerHTML = `
            <p class="m-0">${this.timeDisplayConverter(showtime.startsAt)} - ${this.timeDisplayConverter(showtime.endsAt)}</p>
            <h2>${showtime.movieTitle}</h2>
            <p class="m-0">Format: ${showtime.screeningFormat}</p>
            <a href="${this.showtimeEditUrlValue}/${showtime.id}">Edit</a>
        `;
        showtimeElement.className = "showtime";
        showtimeElement.style.setProperty("--showtimeDuration", `${showtime.durationInMinutes}px`);
        showtimeElement.style.setProperty("--minutesPastHour", `${showtimeDateObject.minutes}px`);

        return showtimeElement;
    }

  
}
