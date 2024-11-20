import { Controller } from '@hotwired/stimulus';
import { dateTimeObjectConverter, timeDisplayConverter } from '../utils/showtime-utils.js';


/* stimulusFetch: 'lazy' */
export default class extends Controller {
    static targets = ['dayAxis', "currentDate", "dateInput"]
    static values = {
        showtimesUrl: String,
        showtimeEditUrl: String,
    }

    showtimes = [];
    showtimeElementClassName = "cinema-showtime-wrapper";
    hourElements = Array.from(this.dayAxisTarget.children);


    connect() {
       this.fetchShowtimes(this.dateInputTarget.value);
       this.currentDateTarget.textContent = this.dateInputTarget.value;
    }

    pickDate(event) {
        const dateTime = event.currentTarget.value.split("T");
        const pickedDate = dateTime.at(0);
        this.currentDateTarget.textContent = pickedDate;
        this.fetchShowtimes(pickedDate);
    }

    clearShowtimeWrapperElements() {
        this.hourElements.forEach(el => {
            const showtimeElements = el.querySelectorAll(`.${this.showtimeElementClassName}`);
            showtimeElements.forEach(showtime => showtime.remove());
        });
    }

    fetchShowtimes(date) {
        fetch(`${this.showtimesUrlValue}/${date}`)
            .then(res => res.json())
            .then(data => {
                this.clearShowtimeWrapperElements();
                this.showtimes = data;
                this.renderShowtimes();
            });
    }


    createScreeningRoomColumnElement(screeningRoomName, leftSpacing) {
        const screeningRoomShowtimesColumn = document.createElement("div");
        screeningRoomShowtimesColumn.textContent = screeningRoomName;
        screeningRoomShowtimesColumn.className = "screening-room-showtime-column";
        screeningRoomShowtimesColumn.style.setProperty("--leftSpacing", leftSpacing);

        return screeningRoomShowtimesColumn;
    }

    createShowtimeWrapperElement(showtime, showtimeDateObject, leftSpacing) {
        const showtimeWrapperElement = document.createElement("div");
        showtimeWrapperElement.className = this.showtimeElementClassName;

        showtimeWrapperElement.style.setProperty("--leftSpacing", leftSpacing);
        showtimeWrapperElement.style.setProperty("--showtimeDuration", `${showtime.durationInMinutes}px`);
        showtimeWrapperElement.style.setProperty("--minutesPastHour", `${showtimeDateObject.minute}px`);

        return showtimeWrapperElement;
    }   

    createShowtimeDetailsElement(showtime) {
        const showtimeDetailsElement = document.createElement("div");
        showtimeDetailsElement.className = "cinema-showtime-details";
        showtimeDetailsElement.innerHTML = `
                <p class="m-0">${timeDisplayConverter(showtime.startsAt)} - ${timeDisplayConverter(showtime.endsAt)}</p>
                <h2>${showtime.movieTitle}</h2>
                <p class="m-0">Format: ${showtime.screeningFormat}</p>
        `;

        return showtimeDetailsElement;
    }


    renderShowtimes() {
        const screeningRooms = Object.keys(this.showtimes);

        screeningRooms.forEach((screeningRoomName, index) => {
            const leftSpacing = ++index * 80 + "px";

            const screeningRoomShowtimesColumn = this.createScreeningRoomColumnElement(screeningRoomName, leftSpacing);
            this.dayAxisTarget.appendChild(screeningRoomShowtimesColumn);

            this.showtimes[screeningRoomName].forEach(showtime => {
                const showtimeDateObject = dateTimeObjectConverter(showtime.startsAt);
                const hourEl = this.hourElements.find(el => Number(el.dataset.hour) === showtimeDateObject.hour);
               
                const showtimeWrapperElement = this.createShowtimeWrapperElement(showtime, showtimeDateObject, leftSpacing);
                const showtimeDetailsElement = this.createShowtimeDetailsElement(showtime);
                
                showtimeWrapperElement.appendChild(showtimeDetailsElement);

                hourEl.appendChild(showtimeWrapperElement);

            });
      
        });


    }



  
}
