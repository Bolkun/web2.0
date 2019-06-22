#!/usr/bin/python

print ('Content-type: text/html')
print ('')

for i in range(5, 11):
    print (i)
    
print ("Denis")

lieblingsEssen = ["Pizza", "Schokolade", "Eis"]

for essen in lieblingsEssen:
    print ("Ich mag " + essen + ".")
    
x = 0
while x <=10:
    print (x)
    x += 1
    
# Dictionary mit 4 Namen (key) und dem Alter (values) von Personen
# Loop der z.b. ausgibt: Serhiy ist 25

alter = {}
alter["Serhiy"] = 25
alter["Hans"] = 35
alter["Heidi"] = 80
alter["Michi"] = 50

for alt in alter:
    print (alt + " ist " + str(alter[alt]))
	
name = "Heidi"

if name == "Denis" or name == "Heidi":
    print ("Hallo " + name)
else:
    print ("Ich kenne dich nicht!")
    
# Erstelle ein Programm das die ersten 50 Primzahlen ausgibt: 2,3,5,7,11,13    

numberOfPrimes = 0
number = 2

while numberOfPrimes < 50:
    isPrime = True
    
    for i in range(2, number):
        if number % i == 0:
            isPrime = False
    
    if isPrime == True:
        print (number)
        numberOfPrimes += 1
    
    number += 1