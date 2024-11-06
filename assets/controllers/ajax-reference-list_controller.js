import { Controller } from '@hotwired/stimulus';
import Dropzone from "dropzone";
import Sortable from "sortablejs";

/* stimulusFetch: 'lazy' */
export default class extends Controller {
    static targets = ["referenceList", "form", "deleteReference"]
        
    static values = {
        url: String
    }

    references = [];

    connect() {
        this.initializeDropzone();
        this.fetchReferences();
        this.sortable = Sortable.create(this.referenceListTarget, {
            handle: ".drag-handle",
            animation: 150,
            onEnd: async () => {
                fetch(this.urlValue + "/reorder", {
                    method: "POST",
                    body: JSON.stringify(this.sortable.toArray())
                }).catch(error => console.log(error))
            }
        });

    }

    initializeDropzone() {
    
        const dropzone = new Dropzone(this.formTarget, {
            paramName: "reference"
        });
    
        dropzone.on("success", (file, data) => {
            this.addReference(data); 
        });
    
        dropzone.on("error", (file, data) => {
            if (data.detail) {
                dropzone.emit("error", file, data.detail);
            }
        });
    }

    addReference(reference) {
        this.references.push(reference);
        this.render();
    }

    async fetchReferences() {
        try {
            const response = await fetch(
                this.urlValue
            );
            const data = await response.json();
            this.references = data;
            this.render();
        } catch (error) {
            console.log(error.message);
        }
    }


    async handleReferenceDelete(event) {
        const li = event.currentTarget.closest(".list-group-item");
        const id = Number(li.dataset.id);

        fetch(`/movie/references/${id}`, {
            method: "DELETE"
        }).then(() => {
            this.references = this.references.filter(reference => reference.id !== id);
            this.render();
        });
       
    }

    handleReferenceEditFilename(event) {
        const li = event.currentTarget.closest(".list-group-item");
        const id = Number(li.dataset.id);

        const reference = this.references.find(reference => reference.id === id);
        reference.originalFilename = event.currentTarget.value;

        fetch(`/movie/references/${id}`, {
            method: "PUT",
            body: JSON.stringify(reference),
            headers: {
                "Content-Type": "application/json"
            }
        }).catch(err => console.log(err));
    }   

    render() {
        const itemsHtml = this.references.map(reference => {
            return `
                <li class="list-group-item d-flex justify-content-between align-items-center" data-id=${reference.id}>
                    <span class="drag-handle">Drag</span>
                    <input data-action="blur->ajax-reference-list#handleReferenceEditFilename" style="width: auto" class="form-control" type="text" value="${reference.originalFilename}" />
                    <span>
                        <a href="/movie/references/${reference.id}/download">
                            ${reference.originalFilename}
                        </a>
                        <button data-action="ajax-reference-list#handleReferenceDelete" class="btn btn-danger">Delete refrence</button>
                    </span>
                </li>
            `
        });

        this.referenceListTarget.innerHTML = itemsHtml.join("");
    }
}
