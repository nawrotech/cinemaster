import { Controller } from '@hotwired/stimulus';


export default class extends Controller {
    static targets = ["collectionHolder",  "desiredRows", "defaultSeatsNumber"]

    static values = {
        index    : Number,
        prototype: String,
        roomMaxCapacityUrl: String
    }

    connect() {
       fetch(this.roomMaxCapacityUrlValue)
            .then(response => response.json())
            .then(data => {
                this.maxRows = data?.maxRows; 
                this.maxSeatsPerRow = data?.maxSeatsPerRow;
            });
            this.seatsPerRow = "";
        
    }



 
  renderRows() {
    const desiredRows = parseInt(this.desiredRowsTarget.value, 10) || this.maxRows;
    
    if (isNaN(desiredRows) || desiredRows < 0) {
      alert("Please enter a valid positive number for rows.");
      this.desiredRowsTarget.value = "";
      return;
    }

    if (desiredRows > this.maxRows) {
      alert(`Maximum number of rows allowed is ${this.maxRows}`);
      this.desiredRowsTarget.value = this.maxRows;
      return;
    }

    // Clear existing rows
    this.collectionHolderTarget.innerHTML = "";

    // Generate new rows
    for (let i = 0; i < desiredRows; i++) {
      this.addRow(i + 1);
    }

    // this.updateRowLabels();
  }

  addRow(rowNumber) {
    const prototype = this.prototypeValue;
    const newFormHtml = prototype.replace(/__name__/g, this.indexValue);
    
    // Create a new div to hold the row
    const rowContainer = document.createElement('div');
    rowContainer.className = 'row-container';
    
    // Create and append the label
    const label = document.createElement('label');
    label.className = 'row-label';
    label.textContent = `Row ${rowNumber}`;
    rowContainer.appendChild(label);
    
    // Append the new form (seats input)
    rowContainer.insertAdjacentHTML('beforeend', newFormHtml);
    
    // Get the newly created input and set its id and name
    const input = rowContainer.querySelector('input');
    input.id = `screening_room_seats_per_row_${this.indexValue}`;
    input.name = `screening_room[seatsPerRow][${this.indexValue}]`;
    input.value = this?.seatsPerRow.value || "";
    
    // Set the label's for attribute
    label.htmlFor = input.id;
    
    // Append the entire row to the collection holder
    this.collectionHolderTarget.appendChild(rowContainer);
    this.indexValue++;

  }

  defaultSeatsNumber() {
    const desiredCols = parseInt(this.defaultSeatsNumberTarget.value, 10) || this.maxSeatsPerRow;
    this.seatsPerRow = this.defaultSeatsNumberTarget;

    if (isNaN(desiredCols) || desiredCols < 0) {
      alert("Please enter a valid positive number for seats per row.");
      this.defaultSeatsNumberTarget.value = "";
      return;
    }


    if (desiredCols > this.maxSeatsPerRow) {
      console.log(this.maxSeatsPerRow);
      alert(`Maximum number of rows allowed for the room in the cinema is ${this.maxSeatsPerRow}`);
      this.defaultSeatsNumberTarget.value = this.maxSeatsPerRow;
      return;
    }


    if (this.indexValue != 0) {
      this.renderRows();
    }

  
  }



}