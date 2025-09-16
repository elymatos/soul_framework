function annotationSetComponent(idAnnotationSet, token, corpusAnnotationType) {
    return {
        idAnnotationSet: null,
        selectionRaw: null,
        selectionNI: null,
        corpusAnnotationType: "fe",
        token: "",

        init() {
            this.idAnnotationSet = idAnnotationSet;
            this.token = token;
            this.corpusAnnotationType = corpusAnnotationType;
        },

        get selection() {
            let type = "", id = "", start = 0, end = 0;
            if (this.selectionRaw) {
                let { anchorNode, anchorOffset, focusNode, focusOffset } = this.selectionRaw;
                var startNode = anchorNode?.parentNode || null;
                var endNode = focusNode?.parentNode || null;
                if ((startNode !== null) && (endNode !== null)) {
                    if (startNode.dataset.type === "word") {
                        type = "word";
                        if (startNode.dataset.startchar) {
                            start = startNode.dataset.startchar;
                        }
                        if (endNode.dataset.endchar) {
                            end = endNode.dataset.endchar;
                        }
                    }
                }
            }
            if (this.selectionNI) {
                type = "ni";
                id = this.selectionNI.dataset.id;
                start = end = 0;
            }
            return {
                type,
                id,
                start,
                end
            };
        },

        onSelectNI(e) {
            this.selectionNI = e;
            let range = new Range();
            range.setStart(e, 0);
            range.setEnd(e, 1);
            document.getSelection().removeAllRanges();
            document.getSelection().addRange(range);
        },

        onLabelAnnotate(idEntity) {
            console.log(this.selection);
            let values = {
                idAnnotationSet: this.idAnnotationSet,
                corpusAnnotationType: this.corpusAnnotationType,
                token: this.token,
                idEntity,
                selection: this.selection
            };
            htmx.ajax("POST", `/annotation/corpus/object`, {
                target: ".annotationSetColumns",
                swap: "innerHTML",
                values: values
            });
        },

        onLabelDelete(idEntity) {
            let values = {
                idAnnotationSet: this.idAnnotationSet,
                corpusAnnotationType: this.corpusAnnotationType,
                token: this.token,
                idEntity
            };
            htmx.ajax("DELETE", `/annotation/corpus/object`, {
                target: ".annotationSetColumns",
                swap: "innerHTML",
                values: values
            });
        },

        onLOMEAccepted(idAnnotationSet) {
            let values = {
                idAnnotationSet,
                corpusAnnotationType: this.corpusAnnotationType,
                token: this.token
            };
            htmx.ajax("POST", `/annotation/corpus/lome/accepted`, {
                target: "#statusField",
                swap: "innerHTML",
                values: values
            });
        }

    };
}
