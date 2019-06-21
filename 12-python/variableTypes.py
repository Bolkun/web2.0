#!/usr/bin/env python
# -*- coding: utf8 -*-

print ("Content-type: text/html\n\n")
print ("Hallo Welt!")

# Das ist ein Zeilenkommentar
# Terminal python -v

"""
Das ist ein Blockkommentar
ueber mehrere Zeilen
"""
name = "Serhiy"
nummer = "5"
str = "Mein Name ist Wolf."
satz = "Mein Name ist "
alter = 25
pi = 3.14
liste = ["Serhiy", "Larysa", "Volker"]
tuple = (1,2,3,4)	# kann nur einmal gesetzt werden und nicht überschrieben werden

print (name, alter)
print (pi)
print (pi * alter)
print (int(nummer) * alter, "\n")

print(str[0])
print(str[0:7])
print(str[0], "\n")

print (satz + name)
print (liste)
print (liste[1])
print (liste[1:2])
print (tuple, "\n")

dict = {}
dict["Vater"] = "Serhiy"
dict["Mutter"] = "Mia"
dict[1] = "Gochan"
dict[2] = "Anna"

print (dict["Vater"])
print (dict.keys())
print (dict.values())


