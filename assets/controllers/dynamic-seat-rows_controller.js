import { Controller } from '@hotwired/stimulus';


export default class extends Controller {
    static targets = ["collectionHolder",  "rows", "seatsInRow"]

    static values = {
        index    : Number,
        prototype: String,
        roomMaxCapacityUrl: String
    }

    connect() {
       fetch(`${this.roomMaxCapacityUrlValue}?` + new URLSearchParams({
          ajaxCall: 1,
       }).toString())
            .then(response => response.json())
            .then(data => {
                this.maxRows = data?.maxRows; 
                this.maxSeatsPerRow = data?.maxSeatsPerRow;
            });
            this.seatsPerRow = "";
        
    }

    validateInteger(target,  max) {
      const integer = parseInt(target, 10);

      if (isNaN(integer) || integer < 0) {
          return "";
      }

      if (integer > max) {
        return max;
      }

      return integer;
    }

 
  renderRows() {
    const validatedRows = this.validateInteger(this.rowsTarget.value, this.maxRows);
    this.rowsTarget.value = validatedRows;

    this.collectionHolderTarget.innerHTML = "";

    for (let i = 0; i < validatedRows; i++) {
      this.addRow(i + 1);
    }

  }

  renderSeatsInRow() {
    const seatsInRowValidated = this.validateInteger(this.seatsInRowTarget.value, this.maxSeatsPerRow);
    this.seatsInRowTarget.value = seatsInRowValidated;

    this.seatsPerRow = this.seatsInRowTarget;

    if (this.indexValue != 0) {
      this.renderRows();
    }

  }

  addRow(rowNumber) {
    const prototype = this.prototypeValue;
    const newFormHtml = prototype.replace(/__name__/g, this.indexValue);
    
    const rowContainer = document.createElement('div');
    rowContainer.className = 'mb-3';
    
    const label = document.createElement('label');
    label.className = 'form-label';
    label.textContent = `Row ${rowNumber}`;
    rowContainer.appendChild(label);
    
    rowContainer.insertAdjacentHTML('beforeend', newFormHtml);
    
    const input = rowContainer.querySelector('input');
    input.id = `screening_room_seats_per_row_${this.indexValue}`;
    input.name = `screening_room[seatsPerRow][${this.indexValue}]`;
    input.value = this?.seatsPerRow.value || "";
    
    label.htmlFor = input.id;
    
    this.collectionHolderTarget.appendChild(rowContainer);
    this.indexValue++;

  }





}