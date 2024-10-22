import { Controller } from '@hotwired/stimulus';


/* stimulusFetch: 'lazy' */
export default class extends Controller {
    static targets = ["collectionContainer", "removeButton"]
    
    static values = {
        index: Number,
        prototype: String,
        wrapperClassName: String
    }

    addCollectionElement()
    {
        const item = document.createElement("li");
        item.classList = this.wrapperClassNameValue;
        item.innerHTML = this.prototypeValue.replace(/__name__/g, this.indexValue);
        this.collectionContainerTarget.appendChild(item);
        this.indexValue++;
    }

    removeElement(event) {
        event.target.closest(`.${this.wrapperClassNameValue}`).remove();
    }

}
