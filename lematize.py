import sys

import nltk
from nltk.corpus import stopwords
from nltk.stem import WordNetLemmatizer
from nltk.stem.porter import PorterStemmer
from pattern.en import tag
import re
from os import listdir
from os.path import isfile, join

nltk.data.path.append("/home/shravan/nltk_data")

def removeStopWords(sentence):
    #removing all special characters
    sentence=' '.join(re.sub('[^A-Za-z0-9]+', '', e) for e in sentence.split())

    sentence=sentence.lower()
    stop = stopwords.words('english')
    a= [i for i in sentence.split() if i not in stop]
    return " ".join(a)
    
def lemmatizeSentence(sentence):
    wordnet_lemmatizer = WordNetLemmatizer()
    resultant=''
    for word,pos in tag(sentence):
        if pos is 'VBP':
            resultant=resultant+" "+wordnet_lemmatizer.lemmatize(word,pos='v')
        else:
            resultant=resultant+" "+wordnet_lemmatizer.lemmatize(word)
    return resultant
    
def stemSentence(sentence):
    porter_stemmer = PorterStemmer()
    resultant=''
    for word in sentence.split():
        resultant=resultant+" "+porter_stemmer.stem(word)
    return resultant
    
def readFile(filename):
    with open(filename) as f:
        content = f.readlines()
    return content
    
def writeFile(filename,contents):
    f = open(filename,'w')
    for c in contents:
        f.write(c+'\n')
    return

noargs = len(sys.argv)
query = "";
for x in range(1,noargs):
	query = query + " "+sys.argv[x];

lmtquery = stemSentence(lemmatizeSentence(removeStopWords(query)))


f = open('/opt/lampp/htdocs/seo/query2.txt','w')

f.write(lmtquery);

f.close()

