with open('inputText.txt', 'r') as myfile:
    data=myfile.read().replace('\n', '')

import nltk
tokens = nltk.word_tokenize(data)

from nltk.tag import CRFTagger
ct = CRFTagger()
ct.set_model_file('all_indo_man_tag_corpus_model.crf.tagger')
hasil = ct.tag_sents([tokens])

tagging = ""
for tokenTag in hasil[0]:
	token,tag = tokenTag
	tagging+=token+"\t"+tag+"\n"

with open("outputText.txt", "w") as text_file:
    text_file.write(tagging)

