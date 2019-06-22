#!/usr/bin/python

print ('Content-type: text/html')
print ('')

def sageHallo():
    print ("Hallo!")
    
sageHallo()

def sageEtwas(etwas):
    print (etwas)
    
sageEtwas("Wie geht es dir?")

def multiplikationVonZwei(x,y):
    return x * y

print (multiplikationVonZwei(4,6))

# Erstelle eine Funktion die den hoechster gemeinsamer Nenner ausgibt 

def hoechsterGemeinsamerNenner(x,y):
    for i in range (1, x + 1):
        if x % i == 0 and y % i == 0:
            hgn = i
    return hgn

print (hoechsterGemeinsamerNenner(2512,1312))

# globale variable
a = 5	
b = 6

def addiereZweiZahlen():
	# interne variable
    a = 10
    c = 5
    return a + b

print ("Summe: ", addiereZweiZahlen()) # gibt nichts aus

# print (c) # gibt nichts aus
