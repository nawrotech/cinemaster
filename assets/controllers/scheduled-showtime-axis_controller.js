import { Controller } from '@hotwired/stimulus';


/* stimulusFetch: 'lazy' */
export default class extends Controller {
    static targets = ['dayAxis', "currentDate",]
    static values = {
        showtimesUrl: String,
    }

    showtimes = [];
    hourElements = Array.from(this.dayAxisTarget.children);

    connect() { // that is obvious connect should be controlled by the child
    //    this.fetchShowtimes(this.showtimeStartsAtValue); // child could make this api call
    //    this.currentDateTarget.textContent = this.showtimeStartsAtValue;
        console.log("hehe");
    }

    pickDate(event) { // stay here
        const dateTime = event.currentTarget.value.split("T");
        const pickedDate = dateTime.at(0);
        this.currentDateTarget.textContent = pickedDate;
        this.fetchShowtimes(pickedDate);

    }


    clearShowtimeElements() { // stay here
        this.hourElements.forEach(el => {
            const showtimeElements = el.querySelectorAll('div'); // somehow make it globally available
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


    attachShowtimesToHourElements(showtimes, createShowtimeElement) {
        showtimes.forEach(showtime => {
            const showtimeDateObject = this.timeObjectConverter(showtime.startsAt);
            const hourEl = this.hourElements.find(el => el.dataset.hour == showtimeDateObject.hours);

            const showtimeElement = createShowtimeElement(showtime);

            hourEl.appendChild(showtimeElement);
        });
    }


    createShowtimeElement() {

    }


    renderShowtimesInRoom() {
        this.attachShowtimesToHourElements(this.showtimes, this.createRoomShowtimeElement);
    }

    leftSpacing = 80;
    renderShowtimesInCinema() {
        const screeningRooms = Object.keys(this.showtimes);

        screeningRooms.forEach((screeningRoom, index) => {
            this.leftSpacing = ++index * this.leftSpacing + "px";

            const screeningRoomShowtimeColumn = this.createCinemaShowtimeColumnElement(screeningRoom);

            this.renderShowtimes(this.showtimes[screeningRoom], this.createCinemaShowtimeElement);

            this.dayAxisTarget.appendChild(screeningRoomShowtimeColumn);

        });
    }



    createCinemaShowtimeColumnElement(screeningRoom) {
        const screeningRoomShowtimeColumn = document.createElement("div");
        screeningRoomShowtimeColumn.textContent = screeningRoom;
        screeningRoomShowtimeColumn.className = "screening-room-showtime-column";
        screeningRoomShowtimeColumn.style.setProperty("--leftSpacing", this.leftSpacing);

        return screeningRoomShowtimeColumn;
    }

    createCinemaShowtimeElement() {
        const showtimeElement = document.createElement("div");
        showtimeElement.className = "cinema-showtime";

        const showtimeDetailsElement = document.createElement("div");
        showtimeDetailsElement.className = "cinema-showtime-details";
        showtimeDetailsElement.innerHTML = `
                <p class="m-0">${this.timeDisplayConverter(showtime.startsAt)} - ${this.timeDisplayConverter(showtime.endsAt)}</p>
                <h2>${showtime.movieTitle}</h2>
                <p class="m-0">Format: ${showtime.screeningFormat}</p>
        `;

        showtimeElement.appendChild(showtimeDetailsElement);
        showtimeElement.style.setProperty("--leftSpacing", `${this.leftSpacing}`);
        showtimeElement.style.setProperty("--showtimeDuration", `${showtime.durationInMinutes}px`);
        showtimeElement.style.setProperty("--minutesPastHour", `${showtimeDateObject.minutes}px`);
    }


    createRoomShowtimeElement(showtime) {
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

}
