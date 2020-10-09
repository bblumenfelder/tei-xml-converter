<template>
    <div class="tei-converter flex flex-col w-full p-8">
        <div v-show="isLoading" class="flex flex-col justify-center items-center w-full">
            <div class="text-3xl text-center">Wird bearbeitet ...</div>
            <iframe src="https://giphy.com/embed/heIX5HfWgEYlW" width="480" height="480" frameBorder="0"
                    class="giphy-embed" allowFullScreen></iframe>
            <p><a href="https://giphy.com/gifs/cat-laptop-document-heIX5HfWgEYlW">via GIPHY</a></p>
        </div>
        <div v-show="this.step === 1 && !this.isLoading" class="step-1 flex flex-col w-full">
            <div>Servus! Gib hier deinen Text ein, den du zu TEI-XML konvertieren möchtest!</div>
            <div class="p-2"><textarea
                v-model="input_text"
                name="" id="ipt_input_text" cols="50" rows="20"
                class="p-2 w-full border rounded border-gray-500"></textarea></div>
            <div class="p-2 flex flex-row justify-end">
                <button
                    @click="convert"
                    class="bg-teal-500 hover:bg-teal-400 text-white font-bold py-2 px-4 border-b-4 border-teal-700 hover:border-teal-500 rounded">
                    Konvertieren!
                </button>
            </div>
        </div>
        <div v-show="this.step === 2 && !this.isLoading" class="step-1 flex flex-col w-full">
            <div class="text-3xl">OMG, IT'S MAGIC!</div>
                <div>Dein konvertierter Text ist fertig!</div>
            <div>
                <h1>Beachte bitte folgendes:</h1>
                <div>
                    <ul class="list-disc text-teal-900 list-inside">
                        <li>Dieses Werkzeug gibt nicht das Endformat aus wie ihr es im Seminar festgelegt habt!</li>
                        <li>Dieses Werkzeug nimmt euch die Arbeit ab, alle Wörter mit w-Tags und alle Satzzeichen mit
                            pc-Tags zu versehen.
                        </li>
                        <li>Richtet euch beim Einfügen z.B. an Romans Vorlage. Achtet darauf, wo die text-Node eingefügt
                            werden muss.
                        </li>
                        <li>Euer Editor hat meist eine Auto-Formatierung; Benutzt diese, das erleichtert einiges</li>
                    </ul>
                </div>
            </div>
            <div class="p-2">
                <div
                    v-text="converted_text"

                    class="p-4 w-full border rounded bg-gray-800 text-teal-300 whitespace-pre-wrap font-mono"></div>
            </div>
        </div>
    </div>
</template>

<script>
import axios from 'axios';
let wait = ms => new Promise((r, j) => setTimeout(r, ms));
export default {
    data: function () {
        return {
            step: 1,
            isLoading: false,
            input_text: 'Miau Miau',
            converted_text: ''
        }
    },
    methods: {
        convert() {
            this.isLoading = true;
            axios.post('/convert', {text_submitted: this.input_text}).then(async response => {
                await wait(3000);
                this.converted_text = response.data;
                this.step = 2;
                this.isLoading = false;
            }).catch( error => {
                console.log(error.response);
            })
        }
    },
    mounted() {
    }
}
</script>
