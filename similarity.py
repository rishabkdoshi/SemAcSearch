"""
Created on Mon Apr 13 18:01:24 2015

@author: shravan
"""
import nltk
from nltk.corpus import wordnet
import sys
from nltk.corpus import stopwords
from nltk.stem import WordNetLemmatizer
from nltk.stem.porter import PorterStemmer
from pattern.en import tag
import re
from os import listdir
from os.path import isfile, join

nltk.data.path.append("/home/shravan/nltk_data")

def removeStopWords(sentence):
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

listofwords = query.split();
ans = "";
for l in listofwords:
    t = wordnet.synsets(l)
    ans2 = ""
    for p in t:
       f = p.lemma_names(); 
       for k in f:
        ans2 = ans2 + " " + k
    ans2 = ans2.split();
    ans2 = list(set(ans2))
    ans3 = ""
    for g in ans2:
        ans3 = ans3 + " "+g
    ans = ans + ' '.join(str(e) for e in list(set(stemSentence(lemmatizeSentence(removeStopWords(ans3))).split()))) +"\n" ;

f = open('/opt/lampp/htdocs/seo/synonyms.txt','w')

f.write(ans);

f.close()
