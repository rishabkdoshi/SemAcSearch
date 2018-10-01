import sys
a = sys.argv[1]
f = open('/opt/lampp/htdocs/seo/query2.txt','w')

f.write(a)

f.close()

for x in range(1,5):
    print x
    
    
def power(x,y):
    s=1
    for t in range(0,y):
       s *=x;
    return s 