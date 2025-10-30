# EXERCISE 3: Analyzing Cars Dataset

# 1. Get details about the dataset
names(cars)
colnames(cars)
rownames(cars)

# 2. View the dataset
View(cars)

# 3. Get data type
str(cars)

# 4. Summary of the data frame
summary(cars)

# 5. Separate by column and row
summary(cars)[, 1]
summary(cars)[, 2]

# 6. Plot speed vs distance
plot(cars$speed, cars$dist, 
     xlab = "speed", 
     ylab = "stop distance", 
     main = "cars data")

# 7. Histograms for speed and distance
hist(cars$speed, main = "Speed Distribution", xlab = "Speed", col = "skyblue")
hist(cars$dist, main = "Distance Distribution", xlab = "Distance", col = "lightcoral")

# 8. ANOVA analysis - create subsets and test
cars.1 <- cars[1:10, ]
cars.2 <- cars[11:20, ]
cars.3 <- cars[21:30, ]
cars.4 <- cars[31:50, ]

anova(lm(dist ~ speed, cars.1), lm(dist ~ speed, cars.2), lm(dist ~ speed, cars.3), lm(dist ~ speed, cars.4))
