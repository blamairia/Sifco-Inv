## 1) vec_1: values of sin(x) for x = 4, 3, 2, 1.5, 3  (radians)
x_input <- c(4, 3, 2, 1.5, 3)
vec_1 <- sin(x_input)

## 2) vec_2: 5.5, cos(3.5), 3, |2+3i|, 3
vec_2 <- c(5.5, cos(3.5), 3, Mod(2 + 3i), 3)

## 3) 2x5 matrix with vec_1 and vec_2 as rows
matrix_result <- rbind(vector_1 = vec_1, vector_2 = vec_2)

## 4) multiply_vectors: element-wise multiplication with broad input support
multiply_vectors <- function(vect1, vect2) {
  coerce_vec <- function(v) {
    # Turn to atomic vector
    if (is.list(v)) v <- unlist(v, recursive = TRUE, use.names = FALSE)

    # Factors -> numeric via character
    if (is.factor(v)) v <- as.character(v)

    # Characters: try numeric; if fails, try complex (e.g., "2+3i")
    if (is.character(v)) {
      suppressWarnings({
        as_num <- as.numeric(v)
      })
      if (all(!is.na(as_num))) return(as_num)

      suppressWarnings({
        as_cplx <- as.complex(v)
      })
      if (all(!is.na(as_cplx))) return(as_cplx)

      stop("Character vector contains non-numeric/non-complex values; cannot multiply.")
    }

    # Logical -> numeric (TRUE=1, FALSE=0)
    if (is.logical(v)) return(as.numeric(v))

    # Numeric/Integer/Complex pass through
    if (is.numeric(v) || is.complex(v)) return(v)

    stop("Unsupported vector type for multiplication.")
  }

  a <- coerce_vec(vect1)
  b <- coerce_vec(vect2)

  # Length checks: allow recycling, warn if not multiple
  if (length(a) == 0 || length(b) == 0) return(a * b) # will be numeric(0)
  if ((max(length(a), length(b)) %% min(length(a), length(b))) != 0) {
    warning("Lengths are not multiples; R will recycle with a warning.")
  }

  a * b
}

## --- Examples ---
# Element-wise multiply vec_1 and vec_2
product_v1_v2 <- multiply_vectors(vec_1, vec_2)

# Works with logicals (coerced to 0/1)
multiply_vectors(c(TRUE, FALSE, TRUE), c(10, 10, 10))

# Works with character numerics and complex strings
multiply_vectors(c("1.5", "2", "3"), c("2+3i", "4", "5-2i"))

# Handles factors by value
multiply_vectors(factor(c("3", "4", "5")), c(2, 2, 2))

# Recycling (with warning if lengths not multiples)
multiply_vectors(1:3, c(10, 20))
