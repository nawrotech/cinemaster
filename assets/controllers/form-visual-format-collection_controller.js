import { Controller } from '@hotwired/stimulus';


/* stimulusFetch: 'lazy' */
export default class extends Controller {
    static targets = ["collectionContainer", "removeButton"]
    
    static values = {
        index: Number,
        prototype: String,
    }

    connect() {
        console.log(this.prototypeValue);
    }

    addCollectionElement(event)
    {
        const item = document.createElement('li');
        item.innerHTML = this.prototypeValue.replace(/__visual_format_name__/g, this.indexValue);
        this.collectionContainerTarget.appendChild(item);
        this.indexValue++;
    }

    removeElement(event) {
        event.target.closest("li").remove();
    }

}
