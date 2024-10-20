import { Controller } from '@hotwired/stimulus';

/* stimulusFetch: 'lazy' */
export default class extends Controller {
    static targets = ['collectionContainer']

    static values = {
        index: Number,
        prototype: String,
    }


    addCollectionElement(event) {
        const item = document.createElement('fieldset');
        item.classList.add('mb-3'); 
        item.innerHTML = this.prototypeValue.replace(/__name__/g, this.indexValue);
        this.addDeleteButton(item);
        this.collectionContainerTarget.appendChild(item);
        this.indexValue++;
    }

    addDeleteButton(fieldset) {
        const removeButton = document.createElement('button');
        removeButton.innerText = 'Delete this format';
        removeButton.classList.add('btn', 'btn-danger', 'mt-2'); 

        fieldset.appendChild(removeButton);

        removeButton.addEventListener('click', (event) => {
            event.preventDefault();
            fieldset.remove(); 
        });
    }

    removeCollectionElement(event) {
        event.target.closest('fieldset').remove();
    }
}
