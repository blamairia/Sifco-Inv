# EXERCISE 2: Vectors, Matrix, Histogram
v1 <- sample(5:60, 30, replace = TRUE)
v2 <- sample(5:60, 30, replace = TRUE)
v3 <- sample(5:60, 30, replace = TRUE)
v4 <- sample(5:60, 30, replace = TRUE)
v5 <- sample(5:60, 30, replace = TRUE)

data_mat <- cbind(v1, v2, v3, v4, v5)
print(head(data_mat, 10))

hist(data_mat[, 3], main = "Histogram of 3rd Column", xlab = "Values", col = "lightblue")
