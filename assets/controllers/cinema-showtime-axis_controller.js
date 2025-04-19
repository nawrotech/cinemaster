import { Controller } from '@hotwired/stimulus';
import { dateTimeObjectConverter, timeDisplayConverter } from '../utils/showtime-utils.js';


/* stimulusFetch: 'lazy' */
export default class extends Controller {
    static targets = ['dayAxis', 'currentDate', 'hour', 'showtime']
    static values = {
        showtimesUrl: String,
        showtimeEditUrl: String,
    }

    static classes = ["showtimeElement"]

    showtimes = [];

    getTodayDate() {
        const today = new Date();
        return today.toISOString().split('T')[0];
    }

    connect() {
        const todayDate = this.getTodayDate();
        this.fetchShowtimes(todayDate);
        this.currentDateTarget.textContent = todayDate;
    }

    pickDate(event) {
        this.clearShowtimeWrapperElements();
        const dateTime = event.currentTarget.value.split("T");
        const pickedDate = dateTime.at(0);
        this.currentDateTarget.textContent = pickedDate;
        this.fetchShowtimes(pickedDate);
    }

    clearShowtimeWrapperElements() {
        this.showtimeTargets.forEach(showtime => showtime.remove());
    }

    fetchShowtimes(date) {
        fetch(`${this.showtimesUrlValue}/${date}`)
            .then(
                res => {
                    if (!res.ok) {
                        throw TypeError();
                    }
                    return res.json()
                })
            .then(data => {
                this.clearShowtimeWrapperElements();
                this.showtimes = data;
                this.renderShowtimes();
            })
            .catch(error => {
                throw error
            });
    }


    createScreeningRoomColumnElement(screeningRoomName, leftSpacing) {
        const screeningRoomShowtimesColumn = document.createElement("div");

        screeningRoomShowtimesColumn.textContent = screeningRoomName;
        screeningRoomShowtimesColumn.className = "screening-room-showtime-column";
        screeningRoomShowtimesColumn.style.setProperty("--leftSpacing", leftSpacing);

        return screeningRoomShowtimesColumn;
    }

    createShowtimeElement(showtime, showtimeDateObject, leftSpacing) {
        const showtimeElement = document.createElement("div");
        showtimeElement.setAttribute('data-cinema-showtime-axis-target', 'showtime')
        showtimeElement.classList.add(this.showtimeElementClass);


        showtimeElement.style.setProperty("--leftSpacing", leftSpacing);
        showtimeElement.style.setProperty("--showtimeDuration", `${showtime.durationInMinutes}px`);
        showtimeElement.style.setProperty("--minutesPastHour", `${showtimeDateObject.minute}px`);

        return showtimeElement;
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
                const hourEl = this.hourTargets.find(el => Number(el.dataset.hour) === showtimeDateObject.hour);
               
                const showtimeElement = this.createShowtimeElement(showtime, showtimeDateObject, leftSpacing);
                const showtimeDetailsElement = this.createShowtimeDetailsElement(showtime);
                
                showtimeElement.appendChild(showtimeDetailsElement);

                if (hourEl) {
                    hourEl.appendChild(showtimeElement);
                }

            });
      
        });


    }



  
}
