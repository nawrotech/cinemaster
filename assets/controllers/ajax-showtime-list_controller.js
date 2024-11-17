import { Controller } from '@hotwired/stimulus';


/* stimulusFetch: 'lazy' */
export default class extends Controller {
    static targets = ['showtimeList', "datePicker"]
    static values = {
        showtimeListUrl: String,
        startsAt: String
    }

    connect() {
        this.fetchShowtimeList(this.startsAtValue, this.startsAtValue);

        console.log(this.datePickerTarget);
    }

    fetchShowtimeList(startDate, endDate) {
        fetch(`${this.showtimeListUrlValue}?${new URLSearchParams({
            showtimeStartTime: startDate ?? "",
            showtimeEndTime: endDate ?? "",
        }).toString()}`)
            .then((res) => res.text())
            .then((html) => this.showtimeListTarget.innerHTML = html)
            .catch((error) => console.error('Error loading showtimes:', error));
    }



    pickDate(event) {
        const dateTime =  event.currentTarget.value.split("T");
        const date = dateTime.at(0);

        this.fetchShowtimeList(date, date);
    }


}
